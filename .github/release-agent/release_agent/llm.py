"""The agent loop: Claude + read-only repo tools + one structured "submit" tool.

Each job gives the model a goal, lets it explore the code with RepoTools, and
ends when the model calls the job's submit tool. The submit tool's input *is*
the job's result, so results are always structured JSON, never free text that
has to be parsed.
"""
from __future__ import annotations

import json
import os
import time
from typing import Any

from .config import Config
from .repo_tools import RepoTools


class AgentError(RuntimeError):
    pass


def _client():
    import anthropic  # imported lazily so --no-llm runs don't need the package
    return anthropic.Anthropic(api_key=os.environ.get("ANTHROPIC_API_KEY"), max_retries=4)


def run_agent(
    cfg: Config,
    *,
    task_name: str,
    system: str,
    user: str,
    submit_tool: dict,
    tools: RepoTools | None = None,
    client: Any = None,
) -> dict:
    """Run the loop and return the submit tool's input."""
    client = client or _client()
    tool_schemas = (tools.schemas() if tools else []) + [submit_tool]
    submit_name = submit_tool["name"]
    max_rounds = int(cfg.llm.get("max_tool_rounds", 25))

    messages: list[dict] = [{"role": "user", "content": user}]
    transcript: list[dict] = []
    usage = {"input_tokens": 0, "output_tokens": 0, "cache_read_input_tokens": 0}

    for round_no in range(1, max_rounds + 1):
        # On the last round, force the model to submit what it has.
        tool_choice = {"type": "tool", "name": submit_name} if round_no == max_rounds else {"type": "auto"}
        resp = client.messages.create(
            model=cfg.llm["model"],
            max_tokens=int(cfg.llm.get("max_tokens", 8000)),
            system=[{"type": "text", "text": system, "cache_control": {"type": "ephemeral"}}],
            tools=tool_schemas,
            tool_choice=tool_choice,
            messages=messages,
        )
        for k in usage:
            usage[k] += getattr(resp.usage, k, 0) or 0

        blocks = [b.model_dump() if hasattr(b, "model_dump") else dict(b) for b in resp.content]
        messages.append({"role": "assistant", "content": blocks})
        transcript.append({"round": round_no, "assistant": blocks})

        tool_uses = [b for b in blocks if b.get("type") == "tool_use"]
        for tu in tool_uses:
            if tu["name"] == submit_name:
                _save_transcript(cfg, task_name, transcript, usage)
                return tu["input"]

        if not tool_uses:
            # Model answered in prose without submitting: nudge it once.
            messages.append({"role": "user", "content": f"Call `{submit_name}` with your final result now."})
            continue

        results = []
        for tu in tool_uses:
            out = tools.dispatch(tu["name"], tu.get("input") or {}) if tools else "ERROR: no tools"
            results.append({"type": "tool_result", "tool_use_id": tu["id"], "content": out[:20000]})
        messages.append({"role": "user", "content": results})
        transcript.append({"round": round_no, "tool_results": [r["content"][:2000] for r in results]})

    _save_transcript(cfg, task_name, transcript, usage)
    raise AgentError(f"{task_name}: model did not submit a result within {max_rounds} rounds")


def _save_transcript(cfg: Config, task: str, transcript: list, usage: dict) -> None:
    cfg.write_output(
        f"{task}-transcript.json",
        json.dumps({"task": task, "model": cfg.llm["model"], "usage": usage,
                    "finished_at": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
                    "transcript": transcript}, indent=2, default=str),
    )
    print(f"[{task}] tokens in={usage['input_tokens']} (cached {usage['cache_read_input_tokens']}) out={usage['output_tokens']}")
