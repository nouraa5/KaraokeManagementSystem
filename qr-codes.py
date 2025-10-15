"""
Bulk QR‑code generator for the karaoke tables – v2
Makes 6 PNGs per table and packs 6 codes per PDF page.

Dependencies (in your activated venv)
-------------------------------------
pip install qrcode[pil] pillow reportlab
"""

import os, qrcode
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont
from reportlab.pdfgen import canvas
from reportlab.lib.units import mm

# ────────────────────────────── 🖊️ EDIT HERE ────────────────────────────── #
OUT_DIR        = Path("qr_out")          # where PNGs go
SPARE_COPIES   = 5       # 1 main + 5 spares  = 6 PNGs per table

REGION_MAP = {           # Region → how many tables
    "L": 15,
    "R": 15,
    "C": 30,
    "B": 60,
}

BASE_URL   = "http://sainteliekaakour.com/karaoke/public/songs.php?table={code}"
FONT_PATH  = r"C:\Windows\Fonts\arial.ttf"   # change to any .ttf you like
FONT_SIZE  = 80                              # very big title
# ─────────────────────────────────────────────────────────────────────────── #

def make_single_qr(text: str) -> Image.Image:
    qr = qrcode.QRCode(error_correction=qrcode.constants.ERROR_CORRECT_M,
                       border=2, box_size=15)
    qr.add_data(text)
    qr.make(fit=True)
    return qr.make_image(fill_color="black", back_color="white").convert("RGB")

def add_label(img: Image.Image, label: str) -> Image.Image:
    font = ImageFont.truetype(FONT_PATH, FONT_SIZE)
    txt_w, txt_h = ImageDraw.Draw(img).textbbox((0, 0), label, font=font)[2:]
    canv = Image.new("RGB", (img.width, img.height + txt_h + 20), "white")
    canv.paste(img, (0, 0))
    draw = ImageDraw.Draw(canv)
    draw.text(((img.width - txt_w) / 2, img.height + 10), label, font=font, fill="black")
    return canv

def build_pngs() -> None:
    OUT_DIR.mkdir(exist_ok=True)
    total_tables = sum(REGION_MAP.values())
    for prefix, count in REGION_MAP.items():
        for i in range(1, count + 1):
            code  = f"{prefix}-{i:02d}"
            url   = BASE_URL.format(code=code)
            label = f"Table {code}"

            img = add_label(make_single_qr(url), label)
            img.save(OUT_DIR / f"{code}.png")

            for s in range(1, SPARE_COPIES + 1):         # spares
                img.save(OUT_DIR / f"{code}_S{s}.png")
    print(f"Saved {total_tables * (1 + SPARE_COPIES)} PNGs → {OUT_DIR}")

def pack_pdf(pdf_name: str = "qr_codes.pdf") -> None:
    codes = sorted(p for p in OUT_DIR.glob("*.png") if "_S" not in p.stem)
    if not codes:
        print("No PNGs found, skipping PDF.")
        return

    # 6 per page → 2 cols × 3 rows
    C_PER_ROW, C_PER_COL, C_SIZE_MM, PAD_MM = 2, 3, 70, 15
    page_w, page_h = 210 * mm, 297 * mm     # A4

    c = canvas.Canvas(pdf_name, pagesize=(page_w, page_h))
    row = col = 0
    for img_path in codes:
        x = PAD_MM * mm + col * (C_SIZE_MM + PAD_MM) * mm
        y = page_h - PAD_MM * mm - (row + 1) * (C_SIZE_MM + PAD_MM) * mm
        c.drawImage(str(img_path), x, y, C_SIZE_MM * mm, C_SIZE_MM * mm)

        col += 1
        if col == C_PER_ROW:
            col = 0
            row += 1
            if row == C_PER_COL:
                row = 0
                c.showPage()
    c.save()
    print(f"Packed {len(codes)} primary codes into {pdf_name}")

if __name__ == "__main__":
    build_pngs()
    pack_pdf()
