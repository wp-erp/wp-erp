"""Read-only tools the model can call to look around the plugin's code.

All paths are confined to the repo root. Nothing here writes or executes
plugin code, so PR content (which is untrusted) cannot make the agent do
anything beyond reading files and producing text.
"""
from __future__ import annotations

import subprocess
from pathlib import Path

MAX_READ_LINES = 400
MAX_SEARCH_HITS = 60


class RepoTools:
    def __init__(self, root: Path):
        self.root = root.resolve()

    def _safe(self, rel: str) -> Path:
        p = (self.root / rel.lstrip("/")).resolve()
        if p != self.root and self.root not in p.parents:
            raise ValueError(f"path outside repository: {rel}")
        return p

    # tools -------------------------------------------------------------------
    def read_file(self, path: str, start_line: int = 1, end_line: int | None = None) -> str:
        p = self._safe(path)
        if not p.is_file():
            return f"ERROR: {path} is not a file"
        lines = p.read_text(encoding="utf-8", errors="replace").splitlines()
        start = max(1, int(start_line))
        end = min(len(lines), int(end_line) if end_line else start + MAX_READ_LINES - 1, start + MAX_READ_LINES - 1)
        body = "\n".join(f"{i:>5}  {lines[i - 1]}" for i in range(start, end + 1))
        more = f"\n... ({len(lines) - end} more lines)" if end < len(lines) else ""
        return f"{path} (lines {start}-{end} of {len(lines)})\n{body}{more}"

    def search_code(self, pattern: str, path_glob: str = "*.php", fixed_string: bool = False) -> str:
        args = ["git", "-C", str(self.root), "grep", "-n", "-I", "--no-color"]
        args += ["-F"] if fixed_string else ["-E"]
        args += ["-e", pattern, "--", path_glob, ":!vendor", ":!node_modules", ":!assets/js", ":!*.min.js"]
        res = subprocess.run(args, capture_output=True, text=True, timeout=60)
        if res.returncode not in (0, 1):
            return f"ERROR: {res.stderr.strip()[:300]}"
        hits = res.stdout.splitlines()
        out = [h[:300] for h in hits[:MAX_SEARCH_HITS]]
        if len(hits) > MAX_SEARCH_HITS:
            out.append(f"... {len(hits) - MAX_SEARCH_HITS} more matches; narrow the pattern or path_glob")
        return "\n".join(out) or "no matches"

    def list_dir(self, path: str = ".") -> str:
        p = self._safe(path)
        if not p.is_dir():
            return f"ERROR: {path} is not a directory"
        entries = sorted(p.iterdir(), key=lambda x: (not x.is_dir(), x.name))
        return "\n".join(f"{e.name}/" if e.is_dir() else e.name for e in entries if e.name != ".git")[:8000]

    # schema -------------------------------------------------------------------
    @staticmethod
    def schemas() -> list[dict]:
        return [
            {
                "name": "read_file",
                "description": "Read lines from a file in the plugin repository (max 400 lines per call). Line numbers are shown.",
                "input_schema": {
                    "type": "object",
                    "properties": {
                        "path": {"type": "string", "description": "Path relative to the repo root"},
                        "start_line": {"type": "integer", "default": 1},
                        "end_line": {"type": "integer"},
                    },
                    "required": ["path"],
                },
            },
            {
                "name": "search_code",
                "description": "git grep across the repository (vendor and built assets excluded). Use it to find hook callers, function definitions, capability checks, etc.",
                "input_schema": {
                    "type": "object",
                    "properties": {
                        "pattern": {"type": "string", "description": "Extended regex (or literal if fixed_string)"},
                        "path_glob": {"type": "string", "default": "*.php"},
                        "fixed_string": {"type": "boolean", "default": False},
                    },
                    "required": ["pattern"],
                },
            },
            {
                "name": "list_dir",
                "description": "List a directory in the repository.",
                "input_schema": {
                    "type": "object",
                    "properties": {"path": {"type": "string", "default": "."}},
                },
            },
        ]

    def dispatch(self, name: str, args: dict) -> str:
        fn = {"read_file": self.read_file, "search_code": self.search_code, "list_dir": self.list_dir}.get(name)
        if not fn:
            return f"ERROR: unknown tool {name}"
        try:
            return fn(**args)
        except Exception as e:  # tool errors go back to the model, they don't crash the run
            return f"ERROR: {type(e).__name__}: {e}"
