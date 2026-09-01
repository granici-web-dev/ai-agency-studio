#!/usr/bin/env bash
# AUSSER DIENST seit 2026-08-15 — Aufgaben laufen über das CRM (.claude/crm.md).
# Bleibt nur für Lesezugriff auf das Trello-Archiv. Nichts Neues hier anlegen.
# Trello helper. Conventions and rules: .claude/trello.md
# Requires: TRELLO_KEY, TRELLO_TOKEN in the environment. Never commit them.
#
# NOTE: no personal data of clients or their customers goes into Trello (US processor).
set -euo pipefail

API="https://api.trello.com/1"

die() { echo "error: $*" >&2; exit 1; }

require_auth() {
  # fall back to a .env next to the repo root, if the vars are not already exported
  if [[ -z "${TRELLO_KEY:-}" || -z "${TRELLO_TOKEN:-}" ]]; then
    local envfile="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/.env"
    if [[ -f "$envfile" ]]; then set -a; . "$envfile"; set +a; fi
  fi
  [[ -n "${TRELLO_KEY:-}"   ]] || die "TRELLO_KEY not set — see .claude/trello.md"
  [[ -n "${TRELLO_TOKEN:-}" ]] || die "TRELLO_TOKEN not set — see .claude/trello.md"
  command -v curl >/dev/null || die "curl not found"
  AUTH="key=${TRELLO_KEY}&token=${TRELLO_TOKEN}"
}

# pretty-print if jq exists, otherwise raw
out() { if command -v jq >/dev/null; then jq "${1:-.}"; else cat; fi; }

req() { # req METHOD PATH [curl args...]
  local method="$1" path="$2"; shift 2
  local sep="?"; [[ "$path" == *\?* ]] && sep="&"
  curl -sS --fail-with-body -X "$method" "${API}${path}${sep}${AUTH}" "$@"
}

DEFAULT_LISTS=("Backlog" "Blockiert" "Bereit" "In Arbeit" "Review" "Freigabe Founder" "Fertig")

usage() {
  cat <<'EOF'
usage: trello.sh <command> [args]

  boards                                  list your boards (id + name)
  lists     <boardId>                     list a board's lists
  cards     <listId>                      list cards in a list
  card      <cardId>                      show one card

  new-board <name>                        create a board with the standard lists
  new-list  <boardId> <name> [pos]        add a list
  set-list  <listId> <name> [pos]         rename / reposition a list

  add       <listId> <name> [desc] [due]  create a card   (due: YYYY-MM-DD)
  move      <cardId> <listId>             move a card
  comment   <cardId> <text>               add a comment
  due       <cardId> <YYYY-MM-DD>         set due date
  close     <cardId>                      archive a card

Card description should carry these four lines (see .claude/trello.md):
  Datei: / Owner: / Abnahme: / Blockiert durch:
EOF
}

cmd="${1:-}"; [[ -n "$cmd" ]] || { usage; exit 1; }
shift || true

case "$cmd" in -h|--help|help) usage; exit 0 ;; esac
require_auth

case "$cmd" in
  boards)
    req GET "/members/me/boards?fields=name,closed" | out '[.[]|select(.closed==false)|{id,name}]'
    ;;
  lists)
    [[ $# -ge 1 ]] || die "lists <boardId>"
    req GET "/boards/$1/lists?fields=name" | out '[.[]|{id,name}]'
    ;;
  cards)
    [[ $# -ge 1 ]] || die "cards <listId>"
    req GET "/lists/$1/cards?fields=name,due,shortUrl" | out '[.[]|{id,name,due,shortUrl}]'
    ;;
  card)
    [[ $# -ge 1 ]] || die "card <cardId>"
    req GET "/cards/$1?fields=name,desc,due,shortUrl,idList" | out
    ;;

  new-board)
    [[ $# -ge 1 ]] || die "new-board <name>"
    board=$(req POST "/boards/?name=$(printf %s "$1" | tr ' ' '+')&defaultLists=false&prefs_permissionLevel=private")
    id=$(printf '%s' "$board" | { command -v jq >/dev/null && jq -r .id || sed -n 's/.*"id":"\([^"]*\)".*/\1/p'; })
    [[ -n "$id" ]] || die "could not read board id from response"
    for l in "${DEFAULT_LISTS[@]}"; do
      req POST "/lists?name=$(printf %s "$l" | tr ' ' '+')&idBoard=${id}&pos=bottom" >/dev/null
      echo "  + list: $l"
    done
    echo "board: $id"
    echo "→ record board and list ids in projects/<slug>/trello.md"
    ;;
  new-list)
    [[ $# -ge 2 ]] || die "new-list <boardId> <name> [pos]"
    req POST "/lists?idBoard=$1&pos=${3:-bottom}" --data-urlencode "name=$2" | out '{id,name,pos}'
    ;;
  set-list)
    [[ $# -ge 2 ]] || die "set-list <listId> <name> [pos]"
    args=(--data-urlencode "name=$2")
    [[ ${3:-} ]] && args+=(--data-urlencode "pos=$3")
    req PUT "/lists/$1" "${args[@]}" | out '{id,name,pos}'
    ;;

  add)
    [[ $# -ge 2 ]] || die "add <listId> <name> [desc] [due]"
    args=(--data-urlencode "name=$2")
    [[ ${3:-} ]] && args+=(--data-urlencode "desc=$3")
    [[ ${4:-} ]] && args+=(--data-urlencode "due=$4")
    req POST "/cards?idList=$1" "${args[@]}" | out '{id,name,shortUrl}'
    ;;
  move)
    [[ $# -ge 2 ]] || die "move <cardId> <listId>"
    req PUT "/cards/$1?idList=$2" | out '{id,name,idList}'
    ;;
  comment)
    [[ $# -ge 2 ]] || die "comment <cardId> <text>"
    req POST "/cards/$1/actions/comments" --data-urlencode "text=$2" | out '{id}'
    ;;
  due)
    [[ $# -ge 2 ]] || die "due <cardId> <YYYY-MM-DD>"
    req PUT "/cards/$1?due=$2" | out '{id,due}'
    ;;
  close)
    [[ $# -ge 1 ]] || die "close <cardId>"
    req PUT "/cards/$1?closed=true" | out '{id,closed}'
    ;;

  -h|--help|help) usage ;;
  *) die "unknown command: $cmd (try: trello.sh help)" ;;
esac
