#!/usr/bin/env bash
# ============================================================
# start-cloudflare.sh
# Menjalankan cloudflared tunnel dan otomatis update APP_URL
# di Laravel .env, lalu restart semua service yang diperlukan.
#
# CARA PAKAI:
#   chmod +x start-cloudflare.sh
#   ./start-cloudflare.sh
# ============================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_ENV="$SCRIPT_DIR/.env"
GATEWAY_DIR="$SCRIPT_DIR/whatsapp-gateway"
LOG_FILE="/tmp/cloudflared_output.log"

echo ""
echo "┌─────────────────────────────────────────┐"
echo "│   Dreamville — Cloudflare Tunnel Setup  │"
echo "└─────────────────────────────────────────┘"
echo ""

# ── 1. Pastikan cloudflared tersedia ─────────────────────────
if ! command -v cloudflared &> /dev/null; then
    echo "❌ cloudflared tidak ditemukan. Install dulu:"
    echo "   curl -L --output cloudflared.deb 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb'"
    echo "   sudo dpkg -i cloudflared.deb"
    exit 1
fi

# ── 2. Pastikan Laravel server sudah jalan ───────────────────
echo "🔍 Cek apakah Laravel sudah berjalan di :8000..."
if ! curl -s --max-time 2 http://127.0.0.1:8000/up &>/dev/null; then
    echo "⚠️  Laravel belum berjalan! Jalankan dulu di terminal lain:"
    echo "   php artisan serve --host=0.0.0.0 --port=8000"
    echo ""
    echo "   Tekan Enter jika sudah dijalankan, atau Ctrl+C untuk batal."
    read -r
fi

# ── 3. Pastikan Node.js Gateway sudah jalan ──────────────────
echo "🔍 Cek apakah WA Gateway sudah berjalan di :3001..."
if ! curl -s --max-time 2 http://127.0.0.1:3001/status -H "Authorization: Bearer dreamville-wa-secret-2024" &>/dev/null; then
    echo "⚠️  Gateway belum berjalan! Menjalankan dengan PM2..."
    cd "$GATEWAY_DIR"
    if command -v pm2 &>/dev/null; then
        pm2 start server.js --name "wa-gateway" 2>/dev/null || pm2 restart wa-gateway 2>/dev/null || true
        sleep 2
    else
        echo "   PM2 tidak ada. Jalankan manual di terminal lain:"
        echo "   cd $GATEWAY_DIR && node server.js"
    fi
    cd "$SCRIPT_DIR"
fi

# ── 4. Mulai cloudflared dan ambil URL-nya ───────────────────
echo ""
echo "🚀 Memulai Cloudflare Tunnel..."
rm -f "$LOG_FILE"

# Jalankan cloudflared di background, arahkan output ke log
cloudflared tunnel --url http://localhost:8000 > "$LOG_FILE" 2>&1 &
CLOUDFLARED_PID=$!

echo "   Menunggu URL tunnel dari Cloudflare..."

# Tunggu URL muncul di log (max 30 detik)
TUNNEL_URL=""
for i in $(seq 1 30); do
    sleep 1
    TUNNEL_URL=$(grep -oP 'https://[a-zA-Z0-9\-]+\.trycloudflare\.com' "$LOG_FILE" 2>/dev/null | head -1)
    if [[ -n "$TUNNEL_URL" ]]; then
        break
    fi
    echo -n "."
done
echo ""

if [[ -z "$TUNNEL_URL" ]]; then
    echo "❌ Gagal mendapatkan URL tunnel. Cek log: $LOG_FILE"
    kill $CLOUDFLARED_PID 2>/dev/null
    exit 1
fi

echo ""
echo "✅ Tunnel aktif!"
echo "   🌐 URL Publik: $TUNNEL_URL"
echo ""

# ── 5. Update APP_URL di Laravel .env ───────────────────────
echo "📝 Mengupdate APP_URL di Laravel .env..."
sed -i "s|^APP_URL=.*|APP_URL=$TUNNEL_URL|" "$LARAVEL_ENV"

# Update ASSET_URL juga jika ada
if grep -q "^ASSET_URL=" "$LARAVEL_ENV"; then
    sed -i "s|^ASSET_URL=.*|ASSET_URL=$TUNNEL_URL|" "$LARAVEL_ENV"
fi

echo "   APP_URL=$TUNNEL_URL"

# ── 6. Bersihkan cache Laravel ──────────────────────────────
echo ""
echo "🧹 Membersihkan Laravel cache..."
cd "$SCRIPT_DIR"
php artisan config:clear --quiet
php artisan route:clear --quiet
php artisan view:clear --quiet
echo "   Cache cleared!"

# ── 7. Tampilkan ringkasan ───────────────────────────────────
echo ""
echo "┌─────────────────────────────────────────────────────────┐"
echo "│  ✅ SEMUA SIAP!                                         │"
echo "│                                                         │"
printf "│  🌐 Akses publik: %-37s│\n" "$TUNNEL_URL"
echo "│  📲 WA Gateway  : http://127.0.0.1:3001                │"
echo "│                                                         │"
echo "│  Ctrl+C untuk menghentikan tunnel.                      │"
echo "└─────────────────────────────────────────────────────────┘"
echo ""
echo "📋 Log cloudflared:"
echo "---"

# ── 8. Cleanup saat Ctrl+C ──────────────────────────────────
cleanup() {
    echo ""
    echo ""
    echo "🛑 Menghentikan cloudflared tunnel..."
    kill $CLOUDFLARED_PID 2>/dev/null

    # Reset APP_URL ke local
    sed -i "s|^APP_URL=.*|APP_URL=http://192.168.1.9:8000|" "$LARAVEL_ENV"
    php artisan config:clear --quiet 2>/dev/null || true

    echo "   APP_URL direset ke http://192.168.1.9:8000"
    echo "   Selesai."
    exit 0
}
trap cleanup SIGINT SIGTERM

# Monitor log sampai dihentikan
tail -f "$LOG_FILE" &
wait $CLOUDFLARED_PID
