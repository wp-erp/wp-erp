"""CLI: python -m release_agent <job> [options]

Jobs
  review          Review a PR (--pr N) or the local branch against --base
  compat-matrix   Print/emit the PHP x WordPress test matrix for Actions
  compat-analyze  Diagnose matrix results (--results DIR [--phpcompat FILE] [--pr N])
  release-prep    Draft changelog, bump versions, run checks, open a release PR
  upstream        Check WordPress/WooCommerce releases, deprecations and dev notes

Common flags
  --repo-root PATH   plugin checkout (default: $GITHUB_WORKSPACE or cwd)
  --dry-run          don't write to GitHub; log intended writes to agent-output/
  --no-llm           deterministic checks only (also automatic without ANTHROPIC_API_KEY)
"""
from __future__ import annotations

import argparse
import sys

from .config import load_config


def main(argv: list[str] | None = None) -> int:
    ap = argparse.ArgumentParser(prog="release_agent", description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("job", choices=["review", "compat-matrix", "compat-analyze", "release-prep", "upstream"])
    ap.add_argument("--repo-root")
    ap.add_argument("--config")
    ap.add_argument("--dry-run", action="store_true")
    ap.add_argument("--no-llm", action="store_true")
    ap.add_argument("--pr", type=int)
    ap.add_argument("--base")
    ap.add_argument("--results")
    ap.add_argument("--phpcompat")
    ap.add_argument("--version")
    ap.add_argument("--bump", default="auto", choices=["auto", "patch", "minor", "major"])
    ap.add_argument("--date")
    ap.add_argument("--update-tested-up-to", action="store_true")
    ap.add_argument("--no-pr", action="store_true")
    a = ap.parse_args(argv)

    cfg = load_config(a.repo_root, a.config, dry_run=a.dry_run, no_llm=a.no_llm)
    if cfg.no_llm and not a.no_llm:
        print("ANTHROPIC_API_KEY not set: running deterministic checks only.")

    if a.job == "review":
        from .jobs import review
        review.run(cfg, pr_number=a.pr, base=a.base)
        return 0
    if a.job == "compat-matrix":
        from .jobs import compat
        compat.matrix(cfg)
        return 0
    if a.job == "compat-analyze":
        from .jobs import compat
        if not a.results:
            ap.error("--results is required")
        return compat.analyze(cfg, a.results, a.phpcompat, a.pr)
    if a.job == "release-prep":
        from .jobs import release
        res = release.run(cfg, version=a.version, bump=a.bump, date=a.date,
                          update_tested_up_to=a.update_tested_up_to, open_pr=not a.no_pr)
        return 2 if res.get("blocking_failed") else 0
    if a.job == "upstream":
        from .jobs import upstream
        upstream.run(cfg)
        return 0
    return 1


if __name__ == "__main__":
    sys.exit(main())
