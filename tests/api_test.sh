#!/usr/bin/env bash
# ============================================================
# Langkah 12 - Skrip uji otomatis alur CRUD (cURL + jq)
#
# Prasyarat : backend berjalan (php -S localhost:8000 -t public)
#             curl dan jq terpasang
# Jalankan  : chmod +x tests/api_test.sh && ./tests/api_test.sh
# Opsional  : BASE_URL=http://localhost:8000/api ./tests/api_test.sh
# ============================================================

BASE_URL="${BASE_URL:-http://localhost:8000/api}"
PASS=0
FAIL=0

for tool in curl jq; do
    if ! command -v "$tool" >/dev/null 2>&1; then
        echo "❌ '$tool' belum terpasang. Pasang dulu, lalu jalankan ulang."
        exit 1
    fi
done

# call METHOD PATH [JSON_BODY]  -> mengisi STATUS dan BODY, lalu menampilkan JSON
call() {
    local method="$1" path="$2" data="${3:-}"
    local tmp
    tmp="$(mktemp)"

    if [ -n "$data" ]; then
        STATUS="$(curl -s -o "$tmp" -w '%{http_code}' -X "$method" "$BASE_URL$path" \
            -H 'Content-Type: application/json' -d "$data")"
    else
        STATUS="$(curl -s -o "$tmp" -w '%{http_code}' -X "$method" "$BASE_URL$path")"
    fi

    BODY="$(cat "$tmp")"
    rm -f "$tmp"

    if [ "$STATUS" = "000" ]; then
        echo "❌ Tidak bisa terhubung ke $BASE_URL — pastikan backend sudah berjalan."
        exit 1
    fi

    echo "$BODY" | jq -c .
}

# check EXPECTED_STATUS LABEL
check() {
    if [ "$STATUS" = "$1" ]; then
        echo "   ✅ $2 → HTTP $STATUS"
        PASS=$((PASS + 1))
    else
        echo "   ❌ $2 → diharapkan HTTP $1, dapat HTTP $STATUS"
        FAIL=$((FAIL + 1))
    fi
}

echo "== Test 1: GET semua buku =="
call GET /books
check 200 "GET /books"

echo
echo "== Test 2: POST buku baru =="
call POST /books '{"judul":"Buku Uji Otomatis","penulis":"Skrip Uji","tahun":2024,"stok":7}'
check 201 "POST /books"
ID="$(echo "$BODY" | jq -r '.data.id')"
echo "ID baru: $ID"

echo
echo "== Test 3: GET buku ID $ID =="
call GET "/books/$ID"
check 200 "GET /books/$ID"

echo
echo "== Test 4: PUT ubah buku =="
call PUT "/books/$ID" '{"judul":"Buku Uji Otomatis (Revisi)","penulis":"Skrip Uji","tahun":2025,"stok":9}'
check 200 "PUT /books/$ID"

echo
echo "== Test 5: DELETE buku =="
call DELETE "/books/$ID"
check 200 "DELETE /books/$ID"

echo
echo "== Test 6: Validasi error =="
call POST /books '{"judul":""}'
check 422 "POST /books tanpa judul"

echo
echo "== Test 7: Data tidak ditemukan & endpoint salah =="
call GET /books/999999
check 404 "GET /books/999999"
call GET /unknown
check 404 "GET /unknown"

echo
echo "-----------------------------------------"
echo "Hasil: $PASS lulus, $FAIL gagal"
[ "$FAIL" -eq 0 ]
