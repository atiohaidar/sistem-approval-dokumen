#!/usr/bin/env bash
set -euo pipefail

# run.sh - start backend (Laravel) and frontend (Nuxt) together.
#
# Behavior:
# - On Linux/macOS (or WSL) it will start backend and frontend in background
#   and stream combined logs to the terminal.
# - On Windows (MSYS/Cygwin/Git-Bash) it will attempt to open two new
#   Command Prompt windows (one for backend, one for frontend) using
#   cmd.exe /c start so you can see the dev servers interactively.
#
# Usage:
#   ./run.sh
#

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"

cd "$ROOT_DIR"

echo "Project root: $ROOT_DIR"

unameOut="$(uname -s 2>/dev/null || echo Unknown)"

start_backend_unix() {
	echo "Starting backend (Laravel) in background..."
	if [ -x "$ROOT_DIR/backend/artisan" ] || [ -f "$ROOT_DIR/backend/artisan" ]; then
		cd "$ROOT_DIR/backend"
		# Use php artisan serve; logs to run-backend.log
		nohup php artisan serve --host=127.0.0.1 --port=8000 > "$ROOT_DIR/run-backend.log" 2>&1 &
		backend_pid=$!
		echo "Backend pid: $backend_pid (logs: $ROOT_DIR/run-backend.log)"
	else
		echo "Cannot find backend/artisan. Skipping backend start."
	fi
}

start_frontend_unix() {
	echo "Starting frontend (Nuxt) in background..."
	if [ -d "$ROOT_DIR/frontend" ]; then
		cd "$ROOT_DIR/frontend"
		nohup npm run dev > "$ROOT_DIR/run-frontend.log" 2>&1 &
		frontend_pid=$!
		echo "Frontend pid: $frontend_pid (logs: $ROOT_DIR/run-frontend.log)"
	else
		echo "Cannot find frontend folder. Skipping frontend start."
	fi
}

start_windows() {
	echo "Detected Windows-like environment. Opening two new cmd windows..."
	# Start backend in a new cmd window
	if [ -f "$ROOT_DIR/backend/artisan" ]; then
		cmd.exe /C start "Backend" cmd /K "cd /d %CD%\\backend && php artisan serve --host=127.0.0.1 --port=8000"
	else
		echo "Backend artisan not found, skipping backend start"
	fi

	# Start frontend in a new cmd window
	if [ -d "$ROOT_DIR/frontend" ]; then
		cmd.exe /C start "Frontend" cmd /K "cd /d %CD%\\frontend && npm run dev"
	else
		echo "Frontend folder not found, skipping frontend start"
	fi
}

case "$unameOut" in
	MINGW*|MSYS*|CYGWIN*|Windows_NT)
		start_windows
		;;
	*)
		start_backend_unix
		start_frontend_unix

		echo "Tailing logs (press Ctrl-C to exit)."
		tail -n +1 -F "$ROOT_DIR/run-backend.log" "$ROOT_DIR/run-frontend.log"
		;;
esac

