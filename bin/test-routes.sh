#!/usr/bin/env bash

BASE_URL="http://localhost:8080"

run() {
    local method=$1
    local url=$2
    local body=$3

    echo ">>> $method $url"

    if [ -n "$body" ]; then
        response=$(curl -s -w "\n%{http_code}" \
            -X "$method" \
            -H "Content-Type: application/json" \
            -d "$body" \
            "$BASE_URL$url")
    else
        response=$(curl -s -w "\n%{http_code}" \
            -X "$method" \
            "$BASE_URL$url")
    fi

    body=$(echo "$response" | head -n -1)
    status=$(echo "$response" | tail -n 1)

    echo "    HTTP $status"
    echo "$body" | python3 -m json.tool 2>/dev/null | sed 's/^/    /' || echo "    $body"
    echo ""
}

echo "=== Cars ==="
run GET    /api/cars
run POST   /api/cars   '{"make":"Toyota","model":"Corolla","buildDate":"2020-01-01","colourId":1}'
run DELETE /api/cars/1

echo ""
echo "=== Colours ==="
run GET   /api/colours
run POST  /api/colours  '{"name":"Green"}'
run PATCH /api/colours/1 '{"name":"Dark Green"}'
