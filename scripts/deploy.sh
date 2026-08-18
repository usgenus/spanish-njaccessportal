#!/usr/bin/env bash
set -e

# =========================================================
# SAFE DEPLOY SCRIPT: SYNC LIVE CMS DATA FIRST BEFORE DEPLOY
# =========================================================

echo "🔄 Fetching live data from https://spanish2.njaccessportal.com..."

LIVE_POSTS=$(curl -s "https://spanish2.njaccessportal.com/api/posts.php" || echo '{"success":false}')
LIVE_VIDEOS=$(curl -s "https://spanish2.njaccessportal.com/api/videos.php" || echo '{"success":false}')
LIVE_BILLBOARDS=$(curl -s "https://spanish2.njaccessportal.com/api/billboards.php" || echo '{"success":false}')

node -e '
const fs = require("fs");
const path = "./data/content.json";

let current = {};
try {
  current = JSON.parse(fs.readFileSync(path, "utf8"));
} catch(e) {
  current = { billboards: [], videos: [], posts: [], categories: {} };
}

try {
  const p = JSON.parse(process.argv[1]);
  if (p && p.success && Array.isArray(p.data) && p.data.length > 0) {
    current.posts = p.data;
    if (p.categories) current.categories.news = p.categories;
  }
} catch(e) {}

try {
  const v = JSON.parse(process.argv[2]);
  if (v && v.success && Array.isArray(v.data) && v.data.length > 0) {
    current.videos = v.data;
    if (v.categories) current.categories.videos = v.categories;
  }
} catch(e) {}

try {
  const b = JSON.parse(process.argv[3]);
  if (b && b.success && Array.isArray(b.data) && b.data.length > 0) {
    current.billboards = b.data;
    if (b.categories) current.categories.billboards = b.categories;
  }
} catch(e) {}

fs.writeFileSync(path, JSON.stringify(current, null, 2), "utf8");
console.log("✅ Live data successfully merged into data/content.json!");
' "$LIVE_POSTS" "$LIVE_VIDEOS" "$LIVE_BILLBOARDS"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
ZIP_FILE="/Users/ejyoon/Desktop/SPANISH_${TIMESTAMP}.zip"

zip -r "$ZIP_FILE" . -x "*.git*" "node_modules/*" "*.zip" "dist/*"

echo "📦 Package created: $ZIP_FILE"
echo "CREATED_ZIP=$ZIP_FILE"
