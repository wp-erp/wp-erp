"""Load release-agent.toml and resolve repo paths."""
from __future__ import annotations

import os
import tomllib
from dataclasses import dataclass, field
from pathlib import Path
from typing import Any

AGENT_DIR = Path(__file__).resolve().parent.parent


@dataclass
class Config:
    raw: dict[str, Any]
    repo_root: Path
    github_repo: str | None = None          # "owner/name"
    dry_run: bool = False
    no_llm: bool = False
    out_dir: Path = field(default_factory=lambda: Path("agent-output"))

    # convenience accessors -------------------------------------------------
    @property
    def plugin(self) -> dict[str, Any]:
        return self.raw["plugin"]

    @property
    def changelog(self) -> dict[str, Any]:
        return self.raw["changelog"]

    @property
    def llm(self) -> dict[str, Any]:
        return self.raw["llm"]

    @property
    def review(self) -> dict[str, Any]:
        return self.raw.get("review", {})

    @property
    def compat(self) -> dict[str, Any]:
        return self.raw.get("compat", {})

    @property
    def upstream(self) -> dict[str, Any]:
        return self.raw.get("upstream", {})

    def path(self, rel: str) -> Path:
        return self.repo_root / rel

    def write_output(self, name: str, text: str) -> Path:
        """Every job writes its result to agent-output/ so it can be inspected
        (and uploaded as an artifact) even when nothing is posted to GitHub."""
        self.out_dir.mkdir(parents=True, exist_ok=True)
        p = self.out_dir / name
        p.write_text(text, encoding="utf-8")
        return p


def load_config(
    repo_root: str | Path | None = None,
    config_path: str | Path | None = None,
    dry_run: bool = False,
    no_llm: bool = False,
) -> Config:
    root = Path(repo_root or os.environ.get("GITHUB_WORKSPACE") or Path.cwd()).resolve()
    cfg_file = Path(config_path) if config_path else AGENT_DIR / "release-agent.toml"
    with open(cfg_file, "rb") as fh:
        raw = tomllib.load(fh)
    out = Path(os.environ.get("AGENT_OUTPUT_DIR", root / "agent-output"))
    return Config(
        raw=raw,
        repo_root=root,
        github_repo=os.environ.get("GITHUB_REPOSITORY"),
        dry_run=dry_run,
        no_llm=no_llm or not os.environ.get("ANTHROPIC_API_KEY"),
        out_dir=out,
    )
