"""
Wrapper script to run the CLI with proper Python path setup.
"""

import sys
import os

# Add project root to Python path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from src.cli import run_menu, run_batch, run_validation_cli, run_merit_calculation

if __name__ == "__main__":
    if len(sys.argv) > 1:
        if sys.argv[1] == "--validate":
            run_validation_cli(prompt_for_paths=False)
        elif sys.argv[1] == "--calculate-merit":
            run_merit_calculation()
        else:
            # Any non-flag argument is a batch input file path
            run_batch(sys.argv[1])
    else:
        run_menu()
