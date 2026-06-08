---
title: Absenin — DESIGN.md
status: draft
created: 2026-06-08
updated: 2026-06-08
---

# DESIGN.md — Absenin

*Visual identity untuk platform presensi Absenin. Mobile app (Flutter) + Web Dashboard (vanilla HTML/CSS).*

## Brand & Style

**Vibe**: Tech-startup modern. Bersih, fungsional, percaya diri — tidak kaku korporat. Mobile-first, whitespace generous, fokus pada clarity dan scanability. Green-dominant untuk trust dan growth, dipadankan dengan neutral gray yang hangat (slate family).

**Voice alignment**: Profesional tapi approachable. Iconography: Phosphor (fill + regular), clean outlines. No skeuomorphism. No gradient-heavy. Bayangan dipakai untuk elevation, bukan dekorasi.

**Platform adaptasi**:
- Mobile (Flutter): Material Design 3 baseline, customized token
- Web Dashboard: CSS custom properties, utility-first

## Colors

```yaml
colors:
  primary:
    50: "#ECFDF5"
    100: "#D1FAE5"
    200: "#A7F3D0"
    300: "#6EE7B7"
    400: "#34D399"
    500: "#10B981"
    600: "#059669"    # ← Primary
    700: "#047857"
    800: "#065F46"
    900: "#064E3B"

  neutral:
    50: "#F8FAFC"
    100: "#F1F5F9"
    200: "#E2E8F0"
    300: "#CBD5E1"
    400: "#94A3B8"
    500: "#64748B"
    600: "#475569"
    700: "#334155"
    800: "#1E293B"
    900: "#0F172A"

  semantic:
    success: "#059669"
    warning: "#F59E0B"
    danger: "#EF4444"
    info: "#3B82F6"

  surface:
    background: "#F8FAFC"
    card: "#FFFFFF"
    elevated: "#FFFFFF"
    overlay: "rgba(15, 23, 42, 0.5)"

  text:
    primary: "#0F172A"
    secondary: "#475569"
    tertiary: "#94A3B8"
    inverse: "#FFFFFF"
    link: "#059669"

  border:
    default: "#E2E8F0"
    focus: "#059669"
    error: "#EF4444"
```

## Typography

```yaml
typography:
  families:
    heading: "Inter"
    body: "Inter"
    mono: "JetBrains Mono"

  scale:
    display-lg:
      family: "Inter"
      size: "36px"
      weight: 700
      lineHeight: 1.2
    display-md:
      family: "Inter"
      size: "28px"
      weight: 700
      lineHeight: 1.25
    heading:
      family: "Inter"
      size: "20px"
      weight: 600
      lineHeight: 1.3
    subheading:
      family: "Inter"
      size: "16px"
      weight: 600
      lineHeight: 1.4
    body:
      family: "Inter"
      size: "14px"
      weight: 400
      lineHeight: 1.5
    body-sm:
      family: "Inter"
      size: "12px"
      weight: 400
      lineHeight: 1.5
    caption:
      family: "Inter"
      size: "11px"
      weight: 500
      lineHeight: 1.4
    button:
      family: "Inter"
      size: "14px"
      weight: 600
      lineHeight: 1.0
```

## Layout & Spacing

```yaml
spacing:
  unit: 4px
  scale:
    xs: "4px"
    sm: "8px"
    md: "12px"
    lg: "16px"
    xl: "20px"
    2xl: "24px"
    3xl: "32px"
    4xl: "40px"
    5xl: "48px"
    6xl: "64px"

  safe_area:
    mobile_horizontal: "16px"
    web_content_max: "1200px"
    web_sidebar: "260px"
```

## Elevation & Depth

```yaml
elevation:
  none:
    shadow: "none"
  sm:
    shadow: "0 1px 2px rgba(15, 23, 42, 0.04)"
  md:
    shadow: "0 2px 8px rgba(15, 23, 42, 0.06)"
  lg:
    shadow: "0 4px 16px rgba(15, 23, 42, 0.08)"
  xl:
    shadow: "0 8px 32px rgba(15, 23, 42, 0.12)"

  # Semantic usage
  card: "sm"
  dropdown: "lg"
  modal: "xl"
  navbar: "md"
```

## Shapes

```yaml
rounded:
  none: "0px"
  sm: "4px"
  md: "8px"
  lg: "12px"
  xl: "16px"
  full: "9999px"

  # Semantic assignments
  button: "md"
  card: "lg"
  input: "md"
  badge: "full"
  modal: "xl"
  avatar: "full"
```

## Components

### Buttons

```yaml
button:
  primary:
    bg: "{colors.primary.600}"
    text: "{colors.text.inverse}"
    rounded: "{rounded.button}"
    height: "44px"
    padding: "0 20px"
    hover_bg: "{colors.primary.700}"
    disabled_bg: "{colors.neutral.300}"

  secondary:
    bg: "{colors.primary.50}"
    text: "{colors.primary.700}"
    border: "1px solid {colors.primary.200}"
    rounded: "{rounded.button}"
    height: "44px"
    padding: "0 20px"

  ghost:
    bg: "transparent"
    text: "{colors.text.secondary}"
    rounded: "{rounded.button}"
    height: "40px"
    hover_bg: "{colors.neutral.100}"

  danger:
    bg: "{colors.semantic.danger}"
    text: "{colors.text.inverse}"
    rounded: "{rounded.button}"
    height: "44px"

  # FAB (Floating Action Button) — Presensi
  fab:
    bg: "{colors.primary.600}"
    icon_color: "{colors.text.inverse}"
    size: "56px"
    rounded: "{rounded.xl}"
    elevation: "{elevation.lg}"
```

### Inputs

```yaml
input:
  default:
    bg: "{colors.surface.card}"
    border: "1px solid {colors.border.default}"
    rounded: "{rounded.input}"
    height: "44px"
    padding: "0 12px"
    text: "{colors.text.primary}"
    placeholder: "{colors.text.tertiary}"
    focus_border: "{colors.border.focus}"
    focus_ring: "0 0 0 3px {colors.primary.100}"

  error:
    border: "1px solid {colors.border.error}"
    focus_ring: "0 0 0 3px rgba(239, 68, 68, 0.15)"

  search:
    bg: "{colors.neutral.100}"
    border: "none"
    rounded: "{rounded.full}"
    height: "40px"
    icon: "{colors.text.tertiary}"
```

### Cards

```yaml
card:
  default:
    bg: "{colors.surface.card}"
    rounded: "{rounded.card}"
    elevation: "{elevation.card}"
    padding: "{spacing.lg}"

  interactive:
    extends: "card.default"
    hover_elevation: "{elevation.md}"
    cursor: "pointer"
    transition: "box-shadow 0.15s ease"
```

### Status Badges

```yaml
badge:
  base:
    rounded: "{rounded.full}"
    padding: "2px 10px"
    font: "{typography.caption}"

  present:
    bg: "{colors.primary.50}"
    text: "{colors.primary.700}"
    dot: "{colors.primary.500}"

  late:
    bg: "#FEF3C7"
    text: "#92400E"
    dot: "{colors.semantic.warning}"

  absent:
    bg: "#FEE2E2"
    text: "#991B1B"
    dot: "{colors.semantic.danger}"

  on_leave:
    bg: "#DBEAFE"
    text: "#1E40AF"
    dot: "{colors.semantic.info}"
```

### Navigation (Mobile)

```yaml
navbar_mobile:
  bg: "{colors.surface.card}"
  border_top: "1px solid {colors.border.default}"
  height: "64px"
  icon_active: "{colors.primary.600}"
  icon_inactive: "{colors.text.tertiary}"
  label_active: "{colors.primary.600}"
  label_inactive: "{colors.text.tertiary}"
  label_size: "{typography.caption.size}"
```

### Sidebar (Web)

```yaml
sidebar:
  width: "260px"
  bg: "{colors.surface.card}"
  border_right: "1px solid {colors.border.default}"
  item_active_bg: "{colors.primary.50}"
  item_active_text: "{colors.primary.700}"
  item_hover_bg: "{colors.neutral.50}"
  section_label: "{typography.caption}"
```

## Do's and Don'ts

### Do
- ✅ Gunakan whitespace generous — jangan crowded
- ✅ Primary action selalu warna hijau solid, satu per screen
- ✅ Status badge selalu dengan dot + text
- ✅ Card untuk grouping konten, bukan sekadar dekorasi
- ✅ Typography hierarchy jelas: display → body → caption
- ✅ Input error state: border merah + helper text, tidak hanya warna
- ✅ FAB untuk aksi presensi — aksi paling penting di mobile

### Don't
- ❌ Jangan shadow berlebihan — max 2 level depth
- ❌ Jangan gradient di background atau button
- ❌ Jangan teks hijau di background putih polos (contrast)
- ❌ Jangan loading spinner tanpa teks konteks
- ❌ Jangan animasi berlebihan — keep it snappy (≤200ms)
- ❌ Jangan label button "Submit" — gunakan kata kerja spesifik ("Clock In", "Ajukan Cuti")
