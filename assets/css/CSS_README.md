# CSS Configuration Guide

## 🎨 Easy Theme Customization

This CSS structure is designed for easy configuration by instructors. All customizable values are centralized in `variables.css`.

## 📁 File Structure

```
css/
├── variables.css      # 🎯 MAIN CONFIGURATION FILE - All colors, sizes, spacing
├── main.css          # 📦 Imports all CSS files (use this in HTML)
├── navbar.css        # 🧭 Navigation bar styles
├── carousel.css      # 🎠 Homepage carousel styles  
├── cards.css         # 📚 Book card and overlay styles
├── footer.css        # 🦶 Footer styles
├── scroll-to-top.css # ⬆️ Scroll to top button
├── utilities.css     # 🔧 Common utility classes
└── responsive.css    # 📱 Mobile responsive styles
```

## 🎯 Quick Configuration

To customize the entire website theme, **only edit `variables.css`**:

### 🎨 Change Colors
```css
/* Primary blue theme */
--primary-color: rgb(9, 105, 218);
--primary-hover: rgb(8, 97, 199);

/* Red buttons/accents */  
--secondary-color: #f00b52;
--secondary-hover: #b20909;

/* Background colors */
--background-dark: #404040;
--background-black: black;
```

### 📏 Change Sizes
```css
/* Carousel height */
--carousel-height: 29rem;

/* Button dimensions */
--rent-btn-width: 60%;
--rent-btn-radius: 200px;

/* Scroll button */
--scroll-btn-size: 2rem;
```

### ⏱️ Change Animation Speed
```css
--transition-fast: 0.3s;
--transition-medium: 0.5s;
```

## 🔧 Component Files

Each component has its own file for easy maintenance:
- **navbar.css**: Navigation menu, dropdown, search bar
- **carousel.css**: Homepage image slider
- **cards.css**: Book cards with hover effects and rent buttons
- **footer.css**: Site footer styling
- **scroll-to-top.css**: Floating scroll-to-top button
- **utilities.css**: Common helper classes (error text, text alignment)

## 📱 Responsive Design

Mobile styles are in `responsive.css` and automatically use the variables from `variables.css`.

## 🚀 Usage

In your HTML files, include only:
```html
<link rel="stylesheet" href="assets/css/main.css" />
```

`main.css` automatically imports all other CSS files in the correct order.

## 📝 Notes

- All hard-coded values have been replaced with CSS custom properties
- Variables follow naming convention: `--[category]-[property]`
- Changes to `variables.css` affect the entire site instantly
- Original `Style.css` has been replaced with this modular system
