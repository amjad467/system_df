import pptxgen from 'pptxgenjs';
import fs from 'fs';
import path from 'path';

const pres = new pptxgen();
pres.layout = 'LAYOUT_16x9';
pres.author = 'بەڕێوەبەرایەتی گومرگی سلێمانی';
pres.company = 'گومرگی سلێمانی • هۆبەی ئۆتۆمبێل';
pres.title = 'سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری';

// Color Palette
const COLORS = {
    white: 'FFFFFF',
    bgLight: 'F8FAFC',
    slateDark: '0F172A',
    slateMuted: '475569',
    slateLight: '94A3B8',
    border: 'E2E8F0',
    
    // Vibrant Stage Colors
    sky: '0284C7',
    skyLight: 'E0F2FE',
    emerald: '059669',
    emeraldLight: 'D1FAE5',
    purple: '7C3AED',
    purpleLight: 'EDE9FE',
    orange: 'EA580C',
    orangeLight: 'FFEDD5',
    amber: 'D97706',
    amberLight: 'FEF3C7',
    rose: 'E11D48',
    roseLight: 'FFE4E6',
    blue: '2563EB',
    blueLight: 'DBEAFE',
    indigo: '4F46E5',
    indigoLight: 'EEF2FF'
};

const FONT_TITLE = 'Arial';
const FONT_BODY = 'Arial';

// Helper: Common Header on standard slides
function addSlideHeader(slide, category, title, subtitle) {
    // Top decorative bar
    slide.addShape(pres.shapes.RECTANGLE, {
        x: 0, y: 0, w: 13.33, h: 0.12,
        fill: { color: COLORS.sky }
    });

    // Category Pill (Right aligned for RTL)
    slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: 10.5, y: 0.35, w: 2.2, h: 0.4,
        rectRadius: 0.2,
        fill: { color: COLORS.skyLight },
        line: { color: COLORS.sky, width: 1 }
    });
    slide.addText(category, {
        x: 10.5, y: 0.35, w: 2.2, h: 0.4,
        fontSize: 11, bold: true, color: COLORS.sky,
        align: 'center', valign: 'middle', rtl: true
    });

    // Slide Title
    slide.addText(title, {
        x: 0.6, y: 0.3, w: 9.8, h: 0.6,
        fontSize: 22, bold: true, color: COLORS.slateDark,
        align: 'right', valign: 'middle', rtl: true
    });

    // Subtitle
    if (subtitle) {
        slide.addText(subtitle, {
            x: 0.6, y: 0.85, w: 9.8, h: 0.35,
            fontSize: 12, color: COLORS.slateMuted,
            align: 'right', valign: 'middle', rtl: true
        });
    }

    // Divider Line
    slide.addShape(pres.shapes.LINE, {
        x: 0.6, y: 1.25, w: 12.13, h: 0,
        line: { color: COLORS.border, width: 1 }
    });

    // Footer
    addSlideFooter(slide);
}

function addSlideFooter(slide) {
    slide.addShape(pres.shapes.LINE, {
        x: 0.6, y: 7.0, w: 12.13, h: 0,
        line: { color: COLORS.border, width: 1 }
    });

    slide.addText('بەڕێوەبەرایەتی گومرگی سلێمانی • هۆبەی ئۆتۆمبێلی گەشتیاری', {
        x: 0.6, y: 7.05, w: 6.0, h: 0.35,
        fontSize: 9, color: COLORS.slateLight,
        align: 'left', valign: 'middle'
    });

    slide.addText('سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری • وەشانی ٢.٠', {
        x: 6.73, y: 7.05, w: 6.0, h: 0.35,
        fontSize: 9, color: COLORS.slateLight,
        align: 'right', valign: 'middle', rtl: true
    });
}

// -------------------------------------------------------------
// SLIDE 1: دەسپێک و بەرگی سەرەکی (Title Cover)
// -------------------------------------------------------------
const s1 = pres.addSlide();
s1.background = { color: COLORS.white };

// Top accent band
s1.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 13.33, h: 0.25,
    fill: { color: COLORS.sky }
});

// Decorative Background Shapes
s1.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 1.0, y: 1.2, w: 11.33, h: 5.2,
    rectRadius: 0.3,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.border, width: 1.5 }
});

// Official Tag Badge
s1.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 4.66, y: 1.7, w: 4.0, h: 0.45,
    rectRadius: 0.2,
    fill: { color: COLORS.skyLight },
    line: { color: COLORS.sky, width: 1 }
});
s1.addText('حکومەتی هەرێمی کوردستان • وەزارەتی دارایی و ئابوری', {
    x: 4.66, y: 1.7, w: 4.0, h: 0.45,
    fontSize: 11, bold: true, color: COLORS.sky,
    align: 'center', valign: 'middle', rtl: true
});

// Main Title
s1.addText('سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری', {
    x: 1.5, y: 2.3, w: 10.33, h: 1.1,
    fontSize: 34, bold: true, color: COLORS.slateDark,
    align: 'center', valign: 'middle', rtl: true
});

// Subtitle
s1.addText('بەڕێوەبەرایەتی گومرگی سلێمانی • هۆبەی ئۆتۆمبێل', {
    x: 2.0, y: 3.4, w: 9.33, h: 0.6,
    fontSize: 20, bold: true, color: COLORS.emerald,
    align: 'center', valign: 'middle', rtl: true
});

s1.addText('دۆسیەی ئەندازیاری، سووڕی کاری قۆناغەکان و ڕاپۆرتی بەدیجیتاڵکردنی تەواوەتی', {
    x: 2.5, y: 4.0, w: 8.33, h: 0.5,
    fontSize: 13, color: COLORS.slateMuted,
    align: 'center', valign: 'middle', rtl: true
});

// 3 Feature Pills at Bottom of Cover
const coverPills = [
    { title: 'بارکۆدی یەکگرتوو', desc: 'بەستنەوەی ٦ قۆناغەکە بە بارکۆد', color: COLORS.sky, bg: COLORS.skyLight, x: 2.0 },
    { title: 'سیستەمی دارایی و یاسایی', desc: 'وەسڵی ۳۷/أ، بەڵێننامە و محاسبة ٦٦', color: COLORS.emerald, bg: COLORS.emeraldLight, x: 5.2 },
    { title: 'پاراستنی تەواوی داتا', desc: 'قوفڵبوونی خودکار و سڕینەوە لە سێرڤەر', color: COLORS.purple, bg: COLORS.purpleLight, x: 8.4 }
];

coverPills.forEach(p => {
    s1.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: p.x, y: 4.75, w: 2.9, h: 1.1,
        rectRadius: 0.2,
        fill: { color: p.bg },
        line: { color: p.color, width: 1.5 }
    });
    s1.addText(p.title, {
        x: p.x + 0.1, y: 4.85, w: 2.7, h: 0.4,
        fontSize: 13, bold: true, color: p.color,
        align: 'center', valign: 'middle', rtl: true
    });
    s1.addText(p.desc, {
        x: p.x + 0.1, y: 5.25, w: 2.7, h: 0.5,
        fontSize: 10, color: COLORS.slateMuted,
        align: 'center', valign: 'top', rtl: true
    });
});

s1.addText('وەشانی سیستەم: 2.0 • بەروار: ٢٠٢٦', {
    x: 1.5, y: 6.6, w: 10.33, h: 0.4,
    fontSize: 10, color: COLORS.slateLight,
    align: 'center', valign: 'middle', rtl: true
});


// -------------------------------------------------------------
// SLIDE 2: ئامانج و دیدگای پڕۆژەکە (Project Vision)
// -------------------------------------------------------------
const s2 = pres.addSlide();
s2.background = { color: COLORS.white };
addSlideHeader(s2, 'دەسپێکی پڕۆژە', 'ئامانج، دیدگا و پێداویستییەکانی دروستکردنی سیستەم', 'بۆچی سیستەمی کۆنی کاغەزی گۆڕدرا بۆ پلاتفۆرمێکی دیجیتاڵی پێشکەوتوو؟');

const visionCards = [
    {
        title: '١. ڕێکخستنی سووڕی کاری فەرمی',
        desc: 'لابردنی پەڕاوگەڕی و تێکەڵبوونی مامەڵەکان؛ دیاریکردنی ٦ قۆناغی زنجیرەیی کە هیچ فەرمانبەرێک ناتوانێت قۆناغی خۆی تێپەڕێنێت بەبێ تەواوبوونی قۆناغی پێشتر.',
        color: COLORS.sky, bg: COLORS.skyLight, x: 0.6
    },
    {
        title: '٢. شەفافیەت و پارێزبەندی دارایی',
        desc: 'حیسابکردنی ئۆتۆماتیکی ڕسوومات، پول، فۆڕم و سزاکان، لەگەڵ وەسڵبڕینی فەرمی ۳۷/أ و ڕێگریکردن لە دووبارەبوونەوەی وەسڵ و دەرهێنانی ڕاپۆرتی محاسبة ٦٦.',
        color: COLORS.emerald, bg: COLORS.emeraldLight, x: 4.65
    },
    {
        title: '٣. پاراستن و بەدواداچوونی داتا',
        desc: 'تۆمارکردنی بارکۆدی زیرەک، قوفڵبوونی فۆرمەکان دوای پشکنین، سیستەمی لۆگی وردبینی (Audit Trail) و بەدواداچوونی تەواوی دەفتەرە بەسەرچووەکان.',
        color: COLORS.purple, bg: COLORS.purpleLight, x: 8.7
    }
];

visionCards.forEach(c => {
    s2.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x, y: 1.6, w: 3.9, h: 4.8,
        rectRadius: 0.25,
        fill: { color: COLORS.bgLight },
        line: { color: c.color, width: 2 }
    });

    s2.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        rectRadius: 0.15,
        fill: { color: c.bg },
        line: { color: c.color, width: 1 }
    });
    s2.addText(c.title, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        fontSize: 14, bold: true, color: c.color,
        align: 'center', valign: 'middle', rtl: true
    });

    s2.addText(c.desc, {
        x: c.x + 0.35, y: 2.7, w: 3.2, h: 3.4,
        fontSize: 13, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true, lineSpacing: 22
    });
});


// -------------------------------------------------------------
// SLIDE 3: جۆرەکانی مامەڵە لە سیستەمدا (7 Transaction Types)
// -------------------------------------------------------------
const s3 = pres.addSlide();
s3.background = { color: COLORS.white };
addSlideHeader(s3, 'جۆرەکانی مامەڵە', 'ناساندنی ٧ جۆری سەرەکی مامەڵەکانی دەفتەر لە سیستەمدا', 'هەر جۆرە مامەڵەیەک نرخ، مەرج، قۆناغ و نوسراوی فەرمی تایبەت بە خۆی هەیە');

const txTypes = [
    { id: '1', name: 'دەرهێنانی دەفتەری نوێ', desc: 'دەرکردنی دەفتەر بۆ یەکەمجار بۆ ماوەی ١ تا ٣ ساڵ، پێویستی بە بۆکسی دەرهێنانە', color: COLORS.emerald, bg: COLORS.emeraldLight },
    { id: '2', name: 'نوێکردنەوەی دەفتەر (تازەکردنەوە)', desc: 'درێژکردنەوەی ماوەی دەفتەر دوای تەواوبوونی، پێویستی بە پشکنین و کەشفە', color: COLORS.sky, bg: COLORS.skyLight },
    { id: '3', name: 'گۆڕینی خاوەنداریەتی (ناوگۆڕین)', desc: 'گواستنەوەی دەفتەر بۆ سەر ناوی شۆفێر یان هاووڵاتییەکی تر', color: COLORS.purple, bg: COLORS.purpleLight },
    { id: '4', name: 'دەفتەرگۆڕین', desc: 'گۆڕینی دەفتەری پڕبوو یان تێکچوو بە دەفتەرێکی نوێ بە هەمان ژمارە و مەرج', color: COLORS.amber, bg: COLORS.amberLight },
    { id: '5', name: 'پووچەڵکردنەوەی دەفتەر', desc: 'گەڕانەوەی هەمیشەیی ئۆتۆمبێل، تۆمارکردنی نوسراوی گومرک و بۆکسی پووچەڵ', color: COLORS.rose, bg: COLORS.roseLight },
    { id: '6', name: 'بەڵێننامەی یاسایی', desc: 'بەڵێننامەی فەرمی هاووڵاتی لەژێر چاودێری فەرمانبەری یاسایی', color: COLORS.orange, bg: COLORS.orangeLight },
    { id: '7', name: 'ناو و دەفتەرگۆڕین (تێکەڵاو)', desc: 'ئەنجامدانی هەردوو مامەڵەی گۆڕینی خاوەنداریەتی و پێدانی دەفتەری نوێ پێکەوە', color: COLORS.indigo, bg: COLORS.indigoLight }
];

// Layout in 2 columns
txTypes.forEach((t, idx) => {
    const col = idx < 4 ? 0 : 1;
    const row = idx < 4 ? idx : idx - 4;
    const x = col === 0 ? 6.8 : 0.6;
    const y = 1.6 + (row * 1.25);
    const w = 5.9;
    const h = 1.15;

    s3.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x, y: y, w: w, h: h,
        rectRadius: 0.2,
        fill: { color: COLORS.bgLight },
        line: { color: t.color, width: 2 }
    });

    // ID Badge
    s3.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x + w - 1.2, y: y + 0.15, w: 1.0, h: 0.4,
        rectRadius: 0.1,
        fill: { color: t.bg }
    });
    s3.addText(`کۆد: ${t.id}`, {
        x: x + w - 1.2, y: y + 0.15, w: 1.0, h: 0.4,
        fontSize: 10, bold: true, color: t.color,
        align: 'center', valign: 'middle', rtl: true
    });

    s3.addText(t.name, {
        x: x + 0.2, y: y + 0.15, w: w - 1.5, h: 0.4,
        fontSize: 13, bold: true, color: t.color,
        align: 'right', valign: 'middle', rtl: true
    });

    s3.addText(t.desc, {
        x: x + 0.2, y: y + 0.55, w: w - 0.4, h: 0.55,
        fontSize: 11, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true
    });
});


// -------------------------------------------------------------
// SLIDE 4: دایەگرامی گەورەی ٦ قۆناغەکە (Big Master Workflow Diagram)
// -------------------------------------------------------------
const s4 = pres.addSlide();
s4.background = { color: COLORS.white };
addSlideHeader(s4, 'هێڵکاری گشتی', 'دایەگرامی گەورەی ٦ قۆناغە سەرەکییەکەی مامەڵە (Workflow Stepper)', 'زنجیرەی بڕیاردان و ڕێڕەوی یاسایی مامەڵە لە هاتنی هاووڵاتییەوە تاوەکو ئەرشیف');

const stagesFlow = [
    { num: '١', title: 'داتائەنتەری', sub: 'تۆمار و بارکۆد', color: COLORS.sky, bg: COLORS.skyLight, x: 11.2 },
    { num: '٢', title: 'کەشف و تەخمین', sub: 'پشکنینی ئەندازیاری', color: COLORS.orange, bg: COLORS.orangeLight, x: 9.1 },
    { num: '٣', title: 'وردبینی یاسایی', sub: 'پەسەندی تێچوو و داتا', color: COLORS.purple, bg: COLORS.purpleLight, x: 7.0 },
    { num: '٤', title: 'وەسڵ و پارەدان', sub: 'وەسڵی ۳۷/أ و دارایی', color: COLORS.emerald, bg: COLORS.emeraldLight, x: 4.9 },
    { num: '٥', title: 'بەشی دەفتەر', sub: 'ژمارەی دەفتەر و چاپ', color: COLORS.blue, bg: COLORS.blueLight, x: 2.8 },
    { num: '٦', title: 'ئەرشیف و بۆکس', sub: 'سەبمیت و قوفڵکردن', color: COLORS.indigo, bg: COLORS.indigoLight, x: 0.7 }
];

// Draw Main Horizontal 6 Stages
stagesFlow.forEach((st, idx) => {
    s4.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: st.x, y: 1.7, w: 1.8, h: 2.5,
        rectRadius: 0.25,
        fill: { color: COLORS.bgLight },
        line: { color: st.color, width: 2.5 }
    });

    // Step Number Badge
    s4.addShape(pres.shapes.OVAL, {
        x: st.x + 0.6, y: 1.9, w: 0.6, h: 0.6,
        fill: { color: st.color }
    });
    s4.addText(st.num, {
        x: st.x + 0.6, y: 1.9, w: 0.6, h: 0.6,
        fontSize: 14, bold: true, color: COLORS.white,
        align: 'center', valign: 'middle'
    });

    s4.addText(st.title, {
        x: st.x + 0.1, y: 2.65, w: 1.6, h: 0.5,
        fontSize: 13, bold: true, color: st.color,
        align: 'center', valign: 'middle', rtl: true
    });

    s4.addText(st.sub, {
        x: st.x + 0.1, y: 3.2, w: 1.6, h: 0.8,
        fontSize: 10, color: COLORS.slateDark,
        align: 'center', valign: 'top', rtl: true
    });

    // Arrow pointing to next stage (from right to left)
    if (idx < stagesFlow.length - 1) {
        s4.addShape(pres.shapes.LEFT_ARROW, {
            x: st.x - 0.28, y: 2.8, w: 0.25, h: 0.3,
            fill: { color: COLORS.slateLight },
            line: { color: COLORS.border, width: 0.5 }
        });
    }
});

// Parallel Legal Box Below
s4.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.7, y: 4.6, w: 12.3, h: 2.1,
    rectRadius: 0.25,
    fill: { color: COLORS.amberLight },
    line: { color: COLORS.amber, width: 2 }
});

s4.addText('قۆناغی هاوتەریب: فەرمانبەری یاسایی (بەڵێننامە)', {
    x: 1.0, y: 4.75, w: 11.7, h: 0.45,
    fontSize: 15, bold: true, color: COLORS.amber,
    align: 'right', valign: 'middle', rtl: true
});

s4.addText('• بەڵێننامەی یاسایی ڕاستەوخۆ پاش داتائەنتەری دەتوانرێت لەلایەن فەرمانبەری یاسایی چاپ بکرێت.\n• هاووڵاتی (بەڵێندەر) لە بەردەم فەرمانبەری یاسایی واژۆی دەکات و هیچ پەیوەستبوونێکی ڕاگرتن بە تەخمین و وردبینی نییە.\n• خاوەنی ٣ بلۆکی واژۆی فەرمییە: [ڕێکخەر لە دەستە ڕاست] • [بارکۆدی فەرمی لە ناوەڕاست] • [بەڕێوەبەری گومرک لە دەستە چەپ].', {
    x: 1.0, y: 5.25, w: 11.7, h: 1.3,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 22
});


// -------------------------------------------------------------
// SLIDE 5: قۆناغی ١ - داتائەنتەری و بارکۆد (Stage 1)
// -------------------------------------------------------------
const s5 = pres.addSlide();
s5.background = { color: COLORS.white };
addSlideHeader(s5, 'قۆناغی یەکەم', 'قۆناغی داتائەنتەری، دروستکردنی بارکۆد و فیشەی بەدواداچوون', 'دەسپێکی تۆمارکردن و دروستکردنی شوناسی دیجیتاڵی مامەڵە');

s5.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 6.8, y: 1.6, w: 5.9, h: 5.0,
    rectRadius: 0.2,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.sky, width: 2 }
});
s5.addText('زانیارییەکانی تۆمارکردنی هاووڵاتی و ئۆتۆمبێل', {
    x: 7.1, y: 1.8, w: 5.3, h: 0.4,
    fontSize: 15, bold: true, color: COLORS.sky,
    align: 'right', valign: 'middle', rtl: true
});
s5.addText('• ناوی هاووڵاتی بە کوردی و ئینگلیزی بەپێی پاسپۆرت\n• ژمارەی مۆبایل و ناونیشانی نیشتەجێبوون\n• شۆفێری دووەم بە کوردی و ئینگلیزی (ئارەزوومەندانە)\n• ژمارەی پاسپۆرت، بەرواری دەرچوون، و لیستی شوێنی دەرچوون\n• ژمارەی تابلۆی ئۆتۆمبێل، جۆری تابلۆ و پارێزگا\n• ژمارەی شاسی ئۆتۆمبێل (VIN) بە گەورەیی و کاپیتال\n• مارکەی ئۆتۆمبێل، ڕەنگ، مۆدێل و ژمارەی بستۆن', {
    x: 7.1, y: 2.3, w: 5.3, h: 4.0,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 24
});

s5.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.6, y: 1.6, w: 5.9, h: 5.0,
    rectRadius: 0.2,
    fill: { color: COLORS.skyLight },
    line: { color: COLORS.sky, width: 2 }
});
s5.addText('بارکۆدی زیرەک و فیشەی بەدواداچوون', {
    x: 0.9, y: 1.8, w: 5.3, h: 0.4,
    fontSize: 15, bold: true, color: COLORS.sky,
    align: 'right', valign: 'middle', rtl: true
});
s5.addText('• دروستبوونی ئۆتۆماتیکی بارکۆدی ١٠ ژمارەیی بە فۆرماتی:\n  YYMMDDXXXX (ساڵ، مانگ، ڕۆژ، زنجیرە)\n• بارکۆد دەبێتە کلیل و مۆری فەرمی سەرجەم قۆناغەکان.\n• چاپی خودکاری فیشەی بەدواداچوونی داتائەنتەری.\n• هەر فەرمانبەرێک تەنها بە سکانکردنی ئەم بارکۆدە مامەڵەکە لە قۆناغی خۆیدا دەبینێت بەبێ پێویستی گەڕانی دەستی.\n• پشتیوانی لە سکانەری خێرای بارکۆد لە سەرتاسەری سیستەم.', {
    x: 0.9, y: 2.3, w: 5.3, h: 4.0,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 24
});


// -------------------------------------------------------------
// SLIDE 6: بەشی یاسایی و بەڵێننامە (Legal Department)
// -------------------------------------------------------------
const s6 = pres.addSlide();
s6.background = { color: COLORS.white };
addSlideHeader(s6, 'بەشی یاسایی', 'بەڵێننامەی یاسایی و دیزاینی بلۆکی ٣ ستوونی واژۆ', 'شیکردنەوەی پێکهاتەی بەڵێننامەی فەرمی و دەسەڵاتی فەرمانبەری یاسایی');

s6.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.6, y: 1.6, w: 12.13, h: 2.1,
    rectRadius: 0.2,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.amber, width: 2 }
});
s6.addText('تایبەتمەندییە سەرەکییەکانی بەڵێننامەی یاسایی:', {
    x: 0.9, y: 1.8, w: 11.5, h: 0.4,
    fontSize: 14, bold: true, color: COLORS.amber,
    align: 'right', valign: 'middle', rtl: true
});
s6.addText('• ئامادەبوونی فەرمی بەڵێندەر لە بەردەم فەرمانبەری یاسایی و خوێندنەوەی دەقی بەڵێننامەی گەڕانەوەی ئۆتۆمبێل.\n• ڕاستەوخۆ پاش داتائەنتەری چاپ دەکرێت و سەربەخۆیە لە قۆناغەکانی کەشف و وردبینی.\n• لە دەسەڵاتی فەرمانبەری یاسایی (Legal Role) و ئەدمیندایە بۆ چاپکردن.', {
    x: 0.9, y: 2.3, w: 11.5, h: 1.2,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 22
});

// 3 Signature Columns Visualization
s6.addText('دیزاینی خوارەوەی بەڵێننامە (٣ ستوونی واژۆ بەپێی ڕێنماییەکان):', {
    x: 0.6, y: 3.9, w: 12.13, h: 0.4,
    fontSize: 14, bold: true, color: COLORS.slateDark,
    align: 'right', valign: 'middle', rtl: true
});

const sigCols = [
    { title: 'دەستە ڕاست: ڕێکخەر', line1: 'ڕێکخەر', line2: 'یاساناس سامان ئەحمەد (فەرمانبەری یاسایی)', line3: 'بەروار: 2026/11/                   ', color: COLORS.sky, bg: COLORS.skyLight, x: 8.7 },
    { title: 'ناوەڕاست: بارکۆدی زیرەک', line1: 'بارکۆدی فەرمی ئۆتۆماتیک', line2: '* 261006001 *', line3: 'کۆدی یەکگرتووی دەفتەر', color: COLORS.purple, bg: COLORS.purpleLight, x: 4.65 },
    { title: 'دەستە چەپ: بەڕێوەبەر', line1: 'قادر پیرۆت قادر', line2: 'بەڕێوەبەری گومرگی سلێمانی', line3: 'بەروار: 2026/11/                   ', color: COLORS.emerald, bg: COLORS.emeraldLight, x: 0.6 }
];

sigCols.forEach(sc => {
    s6.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: sc.x, y: 4.4, w: 3.9, h: 2.3,
        rectRadius: 0.2,
        fill: { color: sc.bg },
        line: { color: sc.color, width: 2 }
    });
    s6.addText(sc.title, {
        x: sc.x + 0.1, y: 4.5, w: 3.7, h: 0.35,
        fontSize: 11, bold: true, color: sc.color,
        align: 'center', valign: 'middle', rtl: true
    });
    s6.addText(sc.line1, {
        x: sc.x + 0.1, y: 4.9, w: 3.7, h: 0.4,
        fontSize: 14, bold: true, color: COLORS.slateDark,
        align: 'center', valign: 'middle', rtl: true
    });
    s6.addText(sc.line2, {
        x: sc.x + 0.1, y: 5.35, w: 3.7, h: 0.4,
        fontSize: 11, color: COLORS.slateMuted,
        align: 'center', valign: 'middle', rtl: true
    });
    s6.addText(sc.line3, {
        x: sc.x + 0.1, y: 5.85, w: 3.7, h: 0.4,
        fontSize: 11, bold: true, color: sc.color,
        align: 'center', valign: 'middle', rtl: true
    });
});


// -------------------------------------------------------------
// SLIDE 7: قۆناغی ٢ - کەشف و تەخمین (Stage 2)
// -------------------------------------------------------------
const s7 = pres.addSlide();
s7.background = { color: COLORS.white };
addSlideHeader(s7, 'قۆناغی دووەم', 'کەشف و تەخمین: پشکنینی فیعلی و مەرجەکانی ئۆتۆمبێل', 'قوفڵکردنی داتائەنتەری و پشتڕاستکردنەوەی مەرجە هونەرییەکان');

const inspectCards = [
    { title: '١. پشکنینی فیعلی شاسی', desc: 'بەراوردکردنی ژمارەی شاسی ئۆتۆمبێل (VIN) لەگەڵ ساڵانە و داتای تۆمارکراو بۆ ڕێگری لە هەر لێکچوون یان هەڵەیەک.', color: COLORS.orange, bg: COLORS.orangeLight, x: 8.7 },
    { title: '٢. قوفڵکردنی دەستکاری داتا', desc: 'دەستبەجێ دوای پەسەندکردنی کەشف لەلایەن ئەندازیارەوە، بەشی داتائەنتەری توانای دەستکاریکردنی زانیارییە سەرەکییەکانی نامێنێت.', color: COLORS.rose, bg: COLORS.roseLight, x: 4.65 },
    { title: '٣. چاپی پسولەی کەشف', desc: 'دەرکردنی پسولەی کەشف و خەمڵاندنی فەرمی بە بارکۆدی زیرەک، دیاریکردنی زیادەبار، کەموکورتی و مەرجە سەلامەتییەکان.', color: COLORS.sky, bg: COLORS.skyLight, x: 0.6 }
];

inspectCards.forEach(c => {
    s7.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x, y: 1.6, w: 3.9, h: 5.0,
        rectRadius: 0.25,
        fill: { color: COLORS.bgLight },
        line: { color: c.color, width: 2 }
    });
    s7.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        rectRadius: 0.15,
        fill: { color: c.bg },
        line: { color: c.color, width: 1 }
    });
    s7.addText(c.title, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        fontSize: 14, bold: true, color: c.color,
        align: 'center', valign: 'middle', rtl: true
    });
    s7.addText(c.desc, {
        x: c.x + 0.35, y: 2.7, w: 3.2, h: 3.4,
        fontSize: 13, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true, lineSpacing: 24
    });
});


// -------------------------------------------------------------
// SLIDE 8: قۆناغی ٣ - وردبینی یاسایی (Stage 3)
// -------------------------------------------------------------
const s8 = pres.addSlide();
s8.background = { color: COLORS.white };
addSlideHeader(s8, 'قۆناغی سێیەم', 'وردبینی: تەئکیدکردنەوەی دارایی و پەسەندکردنی یاسایی', 'پشکنینی داتاکان بە چاوی دووەم و چاپی نوسراوە ئیدارییەکان');

s8.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 6.8, y: 1.6, w: 5.9, h: 5.0,
    rectRadius: 0.2,
    fill: { color: COLORS.purpleLight },
    line: { color: COLORS.purple, width: 2 }
});
s8.addText('تەئکیدی دووانەی بڕی پارە (2-Factor Financial Check)', {
    x: 7.1, y: 1.8, w: 5.3, h: 0.45,
    fontSize: 14, bold: true, color: COLORS.purple,
    align: 'right', valign: 'middle', rtl: true
});
s8.addText('• سیستەم مەرجی داناوە کە وردبین بەبێ دەستنووسکردنی تەواوی بڕی پارەی دیاریکراو ناتوانێت وردبینی تەواو بکات.\n• بەمەش ڕێگری سەدا سەد دەکرێت لە هەر هەڵەیەکی مرۆیی لە بڕی پارەکەدا.\n• ئەگەر ژمارەکە یەکسان نەبێت، سیستەم ئاگاداری سوور پیشان دەدات و دوگمەی پەسەندکردن ناچالاک دەبێت.', {
    x: 7.1, y: 2.4, w: 5.3, h: 3.9,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 24
});

s8.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.6, y: 1.6, w: 5.9, h: 5.0,
    rectRadius: 0.2,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.purple, width: 2 }
});
s8.addText('دەسەڵاتە ئیدارییەکانی قۆناغی وردبینی', {
    x: 0.9, y: 1.8, w: 5.3, h: 0.45,
    fontSize: 14, bold: true, color: COLORS.purple,
    align: 'right', valign: 'middle', rtl: true
});
s8.addText('• چاپی نوسراوی دانانی نیشانەی ڕەفتارنەکردن بۆ بەڕێوەبەرایەتی هاتوچۆ.\n• چاپی نوسراوی پووچەڵکردنەوەی دەفتەر لە کاتی گەڕانەوەی ئۆتۆمبێل.\n• مافی گەڕاندنەوە (Return to Estimator / Data Entry): لە کاتی بوونی هەر هەڵەیەک وردبین هۆکاری گەڕاندنەوە دەنوسێت و دەینێرێتەوە بۆ قۆناغی پێشوو.\n• هەڵوەشاندنەوەی وردبینی پێش پارەدان لە کاتی پێویست.', {
    x: 0.9, y: 2.4, w: 5.3, h: 3.9,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 24
});


// -------------------------------------------------------------
// SLIDE 9: قۆناغی ٤ - وەسڵ و پارەدان (Stage 4)
// -------------------------------------------------------------
const s9 = pres.addSlide();
s9.background = { color: COLORS.white };
addSlideHeader(s9, 'قۆناغی چوارەم', 'وەسڵ و پارەدان: وەرگرتنی پارەی فەرمی و پسوولەی ۳۷/أ', 'ڕێکخستنی دارایی، خەزێنەی دەوڵەت و نوسراوی پشکنین');

const payCards = [
    { title: '١. وەرگرتنی پارەی فەرمی', desc: 'حیسابکردنی بڕی پارەی ساڵەکانی دەفتەر، پول (Stamp)، نرخی فۆڕم، سزای دواکەوتن و کەشف بە شێوەیەکی خودکار.', color: COLORS.emerald, bg: COLORS.emeraldLight, x: 8.7 },
    { title: '٢. تۆمارکردنی وەسڵی ۳۷/أ', desc: 'داخڵکردنی ژمارەی پسوولەی فەرمی ۳۷/أ لەلایەن ژمێریارەوە؛ سیستەم ڕێگری تەواو دەکات لە دووبارەبوونەوەی هەمان ژمارە وەسڵ لە ساڵدا.', color: COLORS.teal, bg: COLORS.emeraldLight, x: 4.65 },
    { title: '٣. چاپی نوسراوی پشکنین', desc: 'لە قۆناغی پارەدان، نوسراوی پشکنین چاپ دەکرێت بە ژمارەی وەسڵی دارایی ۳۷/أ بۆ ئەوەی ڕەوانەی هاتوچۆ بکرێت.', color: COLORS.sky, bg: COLORS.skyLight, x: 0.6 }
];

payCards.forEach(c => {
    s9.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x, y: 1.6, w: 3.9, h: 5.0,
        rectRadius: 0.25,
        fill: { color: COLORS.bgLight },
        line: { color: c.color, width: 2 }
    });
    s9.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        rectRadius: 0.15,
        fill: { color: c.bg },
        line: { color: c.color, width: 1 }
    });
    s9.addText(c.title, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        fontSize: 14, bold: true, color: c.color,
        align: 'center', valign: 'middle', rtl: true
    });
    s9.addText(c.desc, {
        x: c.x + 0.35, y: 2.7, w: 3.2, h: 3.4,
        fontSize: 13, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true, lineSpacing: 24
    });
});


// -------------------------------------------------------------
// SLIDE 10: قۆناغی ٥ - بەشی دەفتەر و کالیبرەیشن (Stage 5)
// -------------------------------------------------------------
const s10 = pres.addSlide();
s10.background = { color: COLORS.white };
addSlideHeader(s10, 'قۆناغی پێنجەم', 'بەشی دەفتەر: پێدانی ژمارەی دەفتەر و ستۆدیۆی چاپ', 'تۆمارکردنی زنجیرەی دەفتەر، کالیبرەیشنی چاپکەر و دەفتەرە بەسەرچووەکان');

s10.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 6.8, y: 1.6, w: 5.9, h: 5.0,
    rectRadius: 0.2,
    fill: { color: COLORS.blueLight },
    line: { color: COLORS.blue, width: 2 }
});
s10.addText('پێدانی دەفتەر و ستۆدیۆی کالیبرەیشن', {
    x: 7.1, y: 1.8, w: 5.3, h: 0.45,
    fontSize: 14, bold: true, color: COLORS.blue,
    align: 'right', valign: 'middle', rtl: true
});
s10.addText('• تۆمارکردنی ژمارەی سەر دەفتەری نێودەوڵەتی لە سیستەم.\n• چاپی دەفتەر ئەکتیڤ نابێت هەتا پارەدان تەواو نەبێت و ژمارە وەرنەگرێت.\n• بوونی ستۆدیۆی کالیبرەیشن (Print Calibration Studio) کە فەرمانبەر دەتوانێت بە ڕاکێشان (Drag-and-Drop) شوێنی نوسینەکان ڕێکبخات بۆ سەرجەم جۆرە جیاوازەکانی چاپکەر.\n• هەڵگرتنی شوێنی چاپی تایبەت بە هەر کۆمپیوتەرێک بە جیاوازی.', {
    x: 7.1, y: 2.4, w: 5.3, h: 3.9,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 24
});

s10.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.6, y: 1.6, w: 5.9, h: 5.0,
    rectRadius: 0.2,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.rose, width: 2 }
});
s10.addText('مۆدیوڵی بەدواداچوونی دەفتەرە بەسەرچووەکان', {
    x: 0.9, y: 1.8, w: 5.3, h: 0.45,
    fontSize: 14, bold: true, color: COLORS.rose,
    align: 'right', valign: 'middle', rtl: true
});
s10.addText('• سیستەم بە شێوەیەکی ئۆتۆماتیکی چاودێری بەرواری بەسەرچوونی دەفتەرەکان دەکات.\n• دەفتەرە بەسەرچووەکان دەخرێنە لیستێکی جیاواز لەگەڵ ژمارەی ڕۆژەکانی دواکەوتن و ناوی خاوەنەکەی.\n• حیسابکردنی ئۆتۆماتیکی سزای دواکەوتن بەپێی یاسا بۆ هەر ڕۆژ یان مانگێکی بەسەرچوو.\n• ئاسانکاری بۆ پەیوەندیکردن بە خاوەن دەفتەر بۆ گەڕاندنەوە یان تازەکردنەوە.', {
    x: 0.9, y: 2.4, w: 5.3, h: 3.9,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 24
});


// -------------------------------------------------------------
// SLIDE 11: قۆناغی ٦ - سەبمیت و ئەرشیف (Stage 6)
// -------------------------------------------------------------
const s11 = pres.addSlide();
s11.background = { color: COLORS.white };
addSlideHeader(s11, 'قۆناغی شەشەم', 'سەبمیت و ئەرشیف: بۆکس فایل و قوفڵکردنی کۆتایی', 'تەواوبوونی مامەڵە، دیاریکردنی شوێنی دۆسیە و پاراستن لە ساختەکاری');

const submitCards = [
    { title: '١. بۆکسی دەرهێنان و پووچەڵ', desc: 'دیاریکردنی ژمارەی بۆکس فایلی دەرهێنان (NO_boxfile) یان بۆکسی پووچەڵ (NO_boxfile_puchal) بۆ دۆزینەوەی خێرای دۆسیەی کاغەزی لە ئەرشیفدا.', color: COLORS.indigo, bg: COLORS.indigoLight, x: 8.7 },
    { title: '٢. قوفڵبوونی تەواوی چاپەکان', desc: 'دوای ئەنجامدانی سەبمیت و ئەرشیف، تەواوی دوگمەکانی چاپ بۆ کارمەندانی ئاسایی قوفڵ دەبن تا هیچ چاپێکی نایاسایی یان دووبارە ئەنجام نەدرێت.', color: COLORS.purple, bg: COLORS.purpleLight, x: 4.65 },
    { title: '٣. دەسەڵاتی تایبەتی ئەدمین', desc: 'تەنها بەڕێوەبەری سیستەم (Admin) لەم قۆناغە بەدواوە دەتوانێت چاپەکان ئەنجام بداتەوە یان لە کاتی پێویست قۆناغەکە هەڵبوەشێنێتەوە.', color: COLORS.rose, bg: COLORS.roseLight, x: 0.6 }
];

submitCards.forEach(c => {
    s11.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x, y: 1.6, w: 3.9, h: 5.0,
        rectRadius: 0.25,
        fill: { color: COLORS.bgLight },
        line: { color: c.color, width: 2 }
    });
    s11.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        rectRadius: 0.15,
        fill: { color: c.bg },
        line: { color: c.color, width: 1 }
    });
    s11.addText(c.title, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        fontSize: 14, bold: true, color: c.color,
        align: 'center', valign: 'middle', rtl: true
    });
    s11.addText(c.desc, {
        x: c.x + 0.35, y: 2.7, w: 3.2, h: 3.4,
        fontSize: 13, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true, lineSpacing: 24
    });
});


// -------------------------------------------------------------
// SLIDE 12: هێڵکاری بەشەکان و دەسەڵاتەکان (RBAC Roles Matrix)
// -------------------------------------------------------------
const s12 = pres.addSlide();
s12.background = { color: COLORS.white };
addSlideHeader(s12, 'دەسەڵاتی بەکارهێنەران', 'خشتەی ٨ ڕۆڵی بەکارهێنەرانی سیستەم و سنووری دەسەڵاتیان', 'پەیڕەوکردنی سیستەمی Role-Based Access Control بۆ پاراستنی بڕیارەکان');

const rolesList = [
    { name: 'Admin (بەڕێوەبەری سیستەم)', desc: 'دەسەڵاتی تەواو بەسەر سەرجەم بەشەکان، هەڵوەشاندنەوەی قۆناغ، ڕێکخستنی نرخ و بەکارهێنەران', color: COLORS.rose, bg: COLORS.roseLight },
    { name: 'Data Entry (داتائەنتەری)', desc: 'تۆمارکردنی مامەڵە، ڕاستکردنەوە پێش کەشف، چارەسەری هەڵەی گەڕێنراوە، چاپی فیشەی بەدواداچوون', color: COLORS.sky, bg: COLORS.skyLight },
    { name: 'Inspector (تەخمین و کەشف)', desc: 'پەسەندکردنی کەشف، دیاریکردنی کەموکورتییەکان، چاپی پسولەی کەشف، گەڕاندنەوە بۆ داتائەنتەری', color: COLORS.orange, bg: COLORS.orangeLight },
    { name: 'Legal (فەرمانبەری یاسایی)', desc: 'خوێندنەوەی دەقی بەڵێننامە، چاپی بەڵێننامەی یاسایی، واژۆکردنی بەڵێننامە وەک ڕێکخەر', color: COLORS.amber, bg: COLORS.amberLight },
    { name: 'Auditor (وردبین)', desc: 'وردبینی داتاکان، پەسەندی تێچوو بە دووجار نوسینەوە، چاپی نوسراوی ڕەفتارنەکردن و پووچەڵ', color: COLORS.purple, bg: COLORS.purpleLight },
    { name: 'Cashier (ژمێریار و وەسڵبڕ)', desc: 'وەرگرتنی پارەی فەرمی، تۆمارکردنی وەسڵی ۳۷/أ، چاپی وەسڵ و نوسراوی پشکنین، ڕاپۆرتی محاسبة ٦٦', color: COLORS.emerald, bg: COLORS.emeraldLight },
    { name: 'Booklet (بەشی دەفتەر)', desc: 'پێدانی ژمارەی دەفتەر، چاپی دەفتەری گەشتیاری بە ستۆدیۆی کالیبرەیشن، بەدواداچوونی بەسەرچووەکان', color: COLORS.blue, bg: COLORS.blueLight },
    { name: 'Archiver (ئەرشیف و تۆمار)', desc: 'تۆمارکردنی ژمارەی بۆکسی دەرهێنان و بۆکسی پووچەڵ، سەبمیتکردن و ئەرشیفکردنی کۆتایی', color: COLORS.indigo, bg: COLORS.indigoLight }
];

rolesList.forEach((r, idx) => {
    const col = idx < 4 ? 0 : 1;
    const row = idx < 4 ? idx : idx - 4;
    const x = col === 0 ? 6.8 : 0.6;
    const y = 1.6 + (row * 1.25);
    const w = 5.9;
    const h = 1.15;

    s12.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x, y: y, w: w, h: h,
        rectRadius: 0.2,
        fill: { color: COLORS.bgLight },
        line: { color: r.color, width: 2 }
    });

    s12.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x + w - 0.25, y: y + 0.15, w: 0.12, h: h - 0.3,
        rectRadius: 0.05,
        fill: { color: r.color }
    });

    s12.addText(r.name, {
        x: x + 0.2, y: y + 0.12, w: w - 0.6, h: 0.4,
        fontSize: 13, bold: true, color: r.color,
        align: 'right', valign: 'middle', rtl: true
    });

    s12.addText(r.desc, {
        x: x + 0.2, y: y + 0.52, w: w - 0.6, h: 0.55,
        fontSize: 10.5, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true
    });
});


// -------------------------------------------------------------
// SLIDE 13: نوسراوە فەرمییە چاپکراوەکان (6 Official Printouts)
// -------------------------------------------------------------
const s13 = pres.addSlide();
s13.background = { color: COLORS.white };
addSlideHeader(s13, 'چاپ و نوسراوەکان', '٦ بەڵگەنامەی فەرمی ستاندارد بە فۆرماتی حکومەتی هەرێم', 'سەرجەم بەڵگەنامەکان دارای لۆگۆی فەرمی، بارکۆد و شوێنی واژۆی دەستنووسن');

const docsList = [
    { title: '١. بەڵێننامەی یاسایی', desc: 'بەڵێننامەی بەڵێندەر لە بەردەم فەرمانبەری یاسایی؛ دارای ٣ ستوونی واژۆ: ڕێکخەر، بارکۆد، و بەڕێوەبەر بە بەرواری دەستنووس.', color: COLORS.amber, bg: COLORS.amberLight },
    { title: '٢. پسوولەی دارایی ۳۷/أ', desc: 'پسوولەی فەرمی وەرگرتنی پارەی خەزێنەی حکومەت لە ژمێریاری، تۆمارکردنی بڕی پارەی ساڵەکان، پول، فۆڕم و غەرامە.', color: COLORS.emerald, bg: COLORS.emeraldLight },
    { title: '٣. نوسراوی ڕەفتارنەکردن', desc: 'نوسراوی گومرک بۆ بەڕێوەبەرایەتی هاتوچۆ بۆ ڕاگرتنی ڕەفتارکردن بە ئۆتۆمبێل لە هاتوچۆ تاوەکو گەڕانەوەی بۆ کوردستان.', color: COLORS.purple, bg: COLORS.purpleLight },
    { title: '٤. نوسراوی پووچەڵکردنەوە', desc: 'نوسراوی گومرک بۆ هاتوچۆ بۆ هەڵگرتنی نیشانەی ڕەفتارنەکردن پاش گەڕانەوەی ئۆتۆمبێل و تەواوبوونی دەفتەرەکە.', color: COLORS.rose, bg: COLORS.roseLight },
    { title: '٥. نووسراوی پشکنین', desc: 'نوسراوی فەرمی لە قۆناغی پارەدان چاپ دەکرێت بە ژمارەی وەسڵی ۳۷/أ بۆ دڵنیابوونەوە لە پاکتاوی دارایی پێش چوونە دەرەوە.', color: COLORS.sky, bg: COLORS.skyLight },
    { title: '٦. پسولەی کەشف و تەخمین', desc: 'پسولەی پشکنینی فیعلی و باری گشتی ئۆتۆمبێل لەگەڵ ژمارەی شاسی و بەرواری دەستپێکردن و بەسەرچوونی دەفتەر.', color: COLORS.orange, bg: COLORS.orangeLight }
];

docsList.forEach((d, idx) => {
    const col = idx < 3 ? 0 : 1;
    const row = idx < 3 ? idx : idx - 3;
    const x = col === 0 ? 6.8 : 0.6;
    const y = 1.6 + (row * 1.65);
    const w = 5.9;
    const h = 1.5;

    s13.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x, y: y, w: w, h: h,
        rectRadius: 0.2,
        fill: { color: COLORS.bgLight },
        line: { color: d.color, width: 2 }
    });

    s13.addText(d.title, {
        x: x + 0.3, y: y + 0.2, w: w - 0.6, h: 0.4,
        fontSize: 14, bold: true, color: d.color,
        align: 'right', valign: 'middle', rtl: true
    });

    s13.addText(d.desc, {
        x: x + 0.3, y: y + 0.65, w: w - 0.6, h: 0.75,
        fontSize: 11, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true, lineSpacing: 20
    });
});


// -------------------------------------------------------------
// SLIDE 14: تابلۆی گرافیکی ئۆتۆمبێل (License Plate Engine)
// -------------------------------------------------------------
const s14 = pres.addSlide();
s14.background = { color: COLORS.white };
addSlideHeader(s14, 'تابلۆی ئۆتۆمبێل', 'مۆدیوڵی تابلۆی گرافیکی هاوشێوەی تابلۆی فەرمی هەرێم', 'دروستکردنی شوناسی بینراوی ئۆتۆمبێل بە ڕەنگ و جۆرە فەرمییەکان');

s14.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.6, y: 1.6, w: 12.13, h: 5.0,
    rectRadius: 0.25,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.sky, width: 2 }
});

s14.addText('پێکهاتەی ئەندازیاری تابلۆی فەرمی لە سیستەمدا:', {
    x: 0.9, y: 1.8, w: 11.5, h: 0.4,
    fontSize: 15, bold: true, color: COLORS.sky,
    align: 'right', valign: 'middle', rtl: true
});

s14.addText('• سیستەم خاوەنی کۆمپۆنێنتێکی گرافیکی تایبەتە کە ڕاستەوخۆ تابلۆی ئۆتۆمبێل بە قەبارەی ڕاستەقینە لەسەر شاشە و فۆڕمەکان دروست دەکات.\n• پشتیوانی لە ٣ جۆری سەرەکی تابلۆ دەکات:\n  ١. تایبەت (Private): باکگراوندی سپی و نوسینی ڕەش لەگەڵ پیتی ستاندارد.\n  ٢. کرێ (Taxi): باکگراوندی سوور و شریتی تاریک بۆ تەکسی.\n  ٣. بارهەڵگر / کشتوکاڵی / گشتی: بە ڕەنگی زەرد و دیزاینی تایبەتی خۆی.\n• شریتی شینی دەستە چەپ خاوەنی لۆگۆی فەرمی هەرێمی کوردستان (KR) و ناوی عێراق (IRAQ)ـە.\n• ناوی پارێزگاکان بە پیتی کوردی بە شێوەیەکی فەرمی پیشان دەدرێت (سلێمانی، هەولێر، دهۆک، هەڵەبجە، کەرکوک، بەغداد، هتد).', {
    x: 0.9, y: 2.3, w: 11.5, h: 4.0,
    fontSize: 13, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 26
});


// -------------------------------------------------------------
// SLIDE 15: سیستەمی دارایی و محاسبة ٦٦ (Financial Engine)
// -------------------------------------------------------------
const s15 = pres.addSlide();
s15.background = { color: COLORS.white };
addSlideHeader(s15, 'دارایی و ژمێریاری', 'سیستەمی دارایی، خەمڵاندنی تێچوو و ڕاپۆرتی محاسبة ٦٦', 'دڵنیابوون لە داهاتی دەوڵەت، پاکتاوی دارایی و ژمێریاری فەرمی');

const finCards = [
    { title: 'بڕی تێچووی ساڵانە', desc: 'دیاریکردنی ڕسووماتی فەرمی بەپێی ژمارەی ساڵەکان (١ تا ٣ ساڵ) بە هاوکێشەی یەکگرتوو.', color: COLORS.emerald, bg: COLORS.emeraldLight, x: 8.7 },
    { title: 'پول و فۆڕم و کەشف', desc: 'حیسابکردنی چەسپاوی ڕسووماتی پولی دارایی، نرخی فۆڕم و کرێی کەشف بە وردی.', color: COLORS.teal, bg: COLORS.emeraldLight, x: 4.65 },
    { title: 'سزای دواکەوتن (غەرامە)', desc: 'دیاریکردنی خودکاری سزای دواکەوتنی گەڕانەوەی دەفتەر بەپێی ڕۆژ و مانگە بەسەرچووەکان.', color: COLORS.amber, bg: COLORS.amberLight, x: 0.6 }
];

finCards.forEach(c => {
    s15.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x, y: 1.6, w: 3.9, h: 2.3,
        rectRadius: 0.2,
        fill: { color: COLORS.bgLight },
        line: { color: c.color, width: 2 }
    });
    s15.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x + 0.3, y: 1.8, w: 3.3, h: 0.45,
        rectRadius: 0.1,
        fill: { color: c.bg }
    });
    s15.addText(c.title, {
        x: c.x + 0.3, y: 1.8, w: 3.3, h: 0.45,
        fontSize: 13, bold: true, color: c.color,
        align: 'center', valign: 'middle', rtl: true
    });
    s15.addText(c.desc, {
        x: c.x + 0.3, y: 2.35, w: 3.3, h: 1.4,
        fontSize: 11, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true, lineSpacing: 20
    });
});

// Accounting 66 Report Section Below
s15.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.6, y: 4.2, w: 12.13, h: 2.5,
    rectRadius: 0.25,
    fill: { color: COLORS.emeraldLight },
    line: { color: COLORS.emerald, width: 2 }
});
s15.addText('ڕاپۆرتی فەرمی محاسبة ٦٦ (خەزێنەی گشتی دەوڵەت)', {
    x: 0.9, y: 4.4, w: 11.5, h: 0.45,
    fontSize: 15, bold: true, color: COLORS.emerald,
    align: 'right', valign: 'middle', rtl: true
});
s15.addText('• سیستەم خاوەنی مۆدیوڵی تایبەتی ڕاپۆرتی فەرمی محاسبة ٦٦ـە کە بەپێی بەروار، وەسڵ و کۆی گشتی پارە دەردەهێنرێت.\n• ژمێریار و بەڕێوەبەر لە کۆتایی هەر ڕۆژ یان مانگێکدا دەتوانن ڕاپۆرتی پاکتاوی خەزێنە بە فۆرماتی وەزارەتی دارایی هەناردە بکەن.\n• وردبینی ئۆتۆماتیکی لە نێوان کۆی وەسڵە بڕدراوەکان و بڕی پارەی ناو خەزێنە بۆ دڵنیابوون لە نەبوونی کورتھێنان.', {
    x: 0.9, y: 4.95, w: 11.5, h: 1.6,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 24
});


// -------------------------------------------------------------
// SLIDE 16: ئاسایش و تۆماری چالاکییەکان (Security & Audit Trail)
// -------------------------------------------------------------
const s16 = pres.addSlide();
s16.background = { color: COLORS.white };
addSlideHeader(s16, 'ئاسایش و چاودێری', 'سیستەمی تۆماری چاودێری و پارێزبەندی چالاکییەکان (Audit Trail)', 'شەفافیەتی تەواو و لێپرسینەوە لە هەر کردەیەک لە ناو سیستەمدا');

const secFeatures = [
    { title: 'تۆماری هەموو کردارێک', desc: 'هەموو دروستکردنێک، دەستکاریکردنێک، کەشف، وردبینی، پارەدان و چاپکردنێک بە وردی لە سیستەمی لۆگ تۆمار دەکرێت.', color: COLORS.sky, bg: COLORS.skyLight, x: 8.7 },
    { title: 'شوناسی فەرمانبەر و کات', desc: 'ناوی فەرمانبەر، کۆدی بەکارهێنەر، بەروار، کات بە چرکە و ناونیشانی IP ی کۆمپیوتەر لەگەڵ چالاکییەکە بەستراوەتەوە.', color: COLORS.purple, bg: COLORS.purpleLight, x: 4.65 },
    { title: 'بەدواداچوونی گەڕاندنەوەکان', desc: 'لە کاتی گەڕاندنەوەی مامەڵە یان ڕێکخستنەوەی قۆناغ لەلایەن ئەدمین، هۆکاری نووسراو دەمێنێتەوە و ناسڕدرێتەوە.', color: COLORS.rose, bg: COLORS.roseLight, x: 0.6 }
];

secFeatures.forEach(c => {
    s16.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x, y: 1.6, w: 3.9, h: 5.0,
        rectRadius: 0.25,
        fill: { color: COLORS.bgLight },
        line: { color: c.color, width: 2 }
    });
    s16.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        rectRadius: 0.15,
        fill: { color: c.bg },
        line: { color: c.color, width: 1 }
    });
    s16.addText(c.title, {
        x: c.x + 0.3, y: 1.9, w: 3.3, h: 0.6,
        fontSize: 14, bold: true, color: c.color,
        align: 'center', valign: 'middle', rtl: true
    });
    s16.addText(c.desc, {
        x: c.x + 0.35, y: 2.7, w: 3.2, h: 3.4,
        fontSize: 13, color: COLORS.slateDark,
        align: 'right', valign: 'top', rtl: true, lineSpacing: 24
    });
});


// -------------------------------------------------------------
// SLIDE 17: پاراستنی داتا و سڕینەوە لە سێرڤەر (Zero-Loss Backup)
// -------------------------------------------------------------
const s17 = pres.addSlide();
s17.background = { color: COLORS.white };
addSlideHeader(s17, 'پارێزبەندی سێرڤەر', 'میکانیزمی هەمیشەیی پاراستنی داتا و گەڕاندنەوەی خودکار', 'ڕێگریکردن لە لەدەستچوونی داتاکان لە کاتی ئەپدەیت یان ڕیستارتی سێرڤەر');

s17.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 0.6, y: 1.6, w: 12.13, h: 5.0,
    rectRadius: 0.25,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.emerald, width: 2.5 }
});

s17.addText('تەلارسازی پاراستنی دوولایەنەی داتا (Dual Redundancy Architecture):', {
    x: 0.9, y: 1.9, w: 11.5, h: 0.45,
    fontSize: 16, bold: true, color: COLORS.emerald,
    align: 'right', valign: 'middle', rtl: true
});

s17.addText('• سیستەمی کۆگای داتاکان خاوەنی میکانیزمی پارێزبەندی خودکارە لە ڕێگەی `DataRestoreService`.\n• هەموو گۆڕانکاری و تۆمارێک لە داتابەیسی زیندوو (`database.sqlite`) هاوکات دەپارێزرێت لە کۆگای یەدەگ (`database_seed.sqlite`).\n• کاتێک سێرڤەر لەسەر ڕێندەر (Render / Cloud) ریستارت دەبێتەوە یان گۆڕانکاری نوێ وەردەگرێت، خزمەتگوزاری چاکسازی دەستبەجێ بەبێ دەستکاری مرۆیی سەرجەم داتاکان و پەیوەندییەکان (Foreign Keys) بە ڕێکی و بێ هەڵە دەگەڕێنێتەوە.\n• داتاکانی هاووڵاتیان، وەسڵەکان و ژمارەی دەفتەرەکان بە پارێزراوی لە سێرڤەر دەمێننەوە و هیچ کات تووشی سڕینەوە نابن.', {
    x: 0.9, y: 2.5, w: 11.5, h: 3.8,
    fontSize: 13, color: COLORS.slateDark,
    align: 'right', valign: 'top', rtl: true, lineSpacing: 28
});


// -------------------------------------------------------------
// SLIDE 18: ئەنجام و کۆتایی (Conclusion & Appreciation)
// -------------------------------------------------------------
const s18 = pres.addSlide();
s18.background = { color: COLORS.white };

// Top accent band
s18.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 13.33, h: 0.25,
    fill: { color: COLORS.emerald }
});

s18.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 1.0, y: 1.2, w: 11.33, h: 5.2,
    rectRadius: 0.3,
    fill: { color: COLORS.bgLight },
    line: { color: COLORS.emerald, width: 2 }
});

s18.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 4.66, y: 1.6, w: 4.0, h: 0.45,
    rectRadius: 0.2,
    fill: { color: COLORS.emeraldLight },
    line: { color: COLORS.emerald, width: 1 }
});
s18.addText('کۆتایی و ئەنجام • دەستکەوتەکانی پڕۆژە', {
    x: 4.66, y: 1.6, w: 4.0, h: 0.45,
    fontSize: 12, bold: true, color: COLORS.emerald,
    align: 'center', valign: 'middle', rtl: true
});

s18.addText('سوپاس و پێزانین بۆ هاوکاری بەڕێوەبەرایەتی گومرگی سلێمانی', {
    x: 1.5, y: 2.2, w: 10.33, h: 0.9,
    fontSize: 28, bold: true, color: COLORS.slateDark,
    align: 'center', valign: 'middle', rtl: true
});

s18.addText('سیستەمی دەرهێنانی دەفتەری ئۆتۆمبێلی گەشتیاری ئێستا بە تەواوی ئامادەیە و بە سەرکەوتوویی جێگیر کراوە.', {
    x: 1.5, y: 3.1, w: 10.33, h: 0.5,
    fontSize: 14, color: COLORS.slateMuted,
    align: 'center', valign: 'middle', rtl: true
});

// Summary Milestones Box
s18.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 2.0, y: 3.8, w: 9.33, h: 1.8,
    rectRadius: 0.2,
    fill: { color: COLORS.white },
    line: { color: COLORS.border, width: 1.5 }
});

s18.addText('• بەدیجیتاڵکردنی ١٠٠٪ سووڕی کاری دەفتەر لە سەرەتا تا ئەرشیف بە ٦ قۆناغی ستاندارد\n• یەکخستنی دارایی و پشکنین بە وەسڵی فەرمی ۳۷/أ و ڕاپۆرتی محاسبة ٦٦\n• چاپی بەڵێننامەی یاسایی بە ٣ ستوونی واژۆ و دەرکەوتنی گەورەی جۆری مامەڵە لە کاتی لێدانی بارکۆد\n• پاراستنی داتا و توانای بەکارهێنانی لەسەر سێرڤەر بە بەرزترین ئاستی خێرایی و پارێزبەندی', {
    x: 2.3, y: 4.0, w: 8.73, h: 1.4,
    fontSize: 12, color: COLORS.slateDark,
    align: 'right', valign: 'middle', rtl: true, lineSpacing: 22
});

s18.addText('بەڕێوەبەرایەتی گومرگی سلێمانی • هۆبەی ئۆتۆمبێلی گەشتیاری • ساڵی ٢٠٢٦', {
    x: 1.5, y: 6.6, w: 10.33, h: 0.4,
    fontSize: 10.5, color: COLORS.slateLight,
    align: 'center', valign: 'middle', rtl: true
});


// -------------------------------------------------------------
// SAVE PRESENTATION FILE
// -------------------------------------------------------------
const outputPublicPath = path.resolve('public', 'tourist_car_system_presentation.pptx');
const outputArtifactPath = 'C:\\Users\\PC\\.gemini\\antigravity\\brain\\69e1b5d7-67fb-49af-8a2c-d7ca110d43ac\\tourist_car_system_presentation.pptx';

pres.writeFile({ fileName: outputPublicPath })
    .then(() => {
        console.log(`Successfully generated PowerPoint file at: ${outputPublicPath}`);
        // Copy to artifact folder
        fs.copyFileSync(outputPublicPath, outputArtifactPath);
        console.log(`Copied successfully to artifact folder at: ${outputArtifactPath}`);
    })
    .catch(err => {
        console.error('Error generating presentation:', err);
        process.exit(1);
    });
