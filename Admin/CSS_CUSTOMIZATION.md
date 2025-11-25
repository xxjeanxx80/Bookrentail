# HƯỚNG DẪN TÙY CHỈNH CSS ADMIN PANEL

## 🎨 Cách Thức Tùy Chỉnh Màu Sắc và Kích Thước

### 1. Thay Đổi Màu Chủ Đạo
Mở file `css/admin.css` và chỉnh sửa các biến trong phần `:root`:

```css
:root {
    /* Màu chủ đạo - Thay đổi các giá trị này */
    --primary-color: #2c3e50;      /* Màu chính của navbar và headings */
    --primary-hover: #34495e;      /* Màu khi hover */
    --primary-light: #ecf0f1;      /* Màu nền light */
    
    /* Màu phụ - Màu của buttons */
    --secondary-color: #3498db;    /* Màu xanh dương */
    --secondary-hover: #2980b9;    /* Màu xanh dương đậm */
    
    /* Màu trạng thái */
    --success-color: #27ae60;      /* Màu xanh lá (thành công) */
    --warning-color: #f39c12;      /* Màu vàng (cảnh báo) */
    --danger-color: #e74c3c;       /* Màu đỏ (lỗi) */
    
    /* Màu nền */
    --bg-primary: #ffffff;         /* Nền trắng */
    --bg-secondary: #f8f9fa;       /* Nền xám nhạt */
    --bg-light: #fbfbfb;           /* Nền rất nhạt */
    
    /* Màu chữ */
    --text-primary: #2c3e50;       /* Chữ chính */
    --text-secondary: #6c757d;     /* Chữ phụ */
    --text-light: #95a5a6;         /* Chữ nhạt */
}
```

### 2. Thay Đổi Kích Thước và Hiệu Ứng

```css
:root {
    /* Kích thước */
    --border-radius: 8px;          /* Bo góc */
    --box-shadow: 0 2px 10px rgba(0,0,0,0.1);  /* Đổ bóng */
    --transition: all 0.3s ease;   /* Hiệu ứng chuyển động */
}
```

## 🎯 Các Màu Sắc Phổ Biến

### Theme Xanh Dương (Mặc định)
```css
--primary-color: #2c3e50;
--secondary-color: #3498db;
```

### Theme Xanh Lá
```css
--primary-color: #27ae60;
--secondary-color: #2ecc71;
```

### Theme Đỏ
```css
--primary-color: #c0392b;
--secondary-color: #e74c3c;
```

### Theme Tím
```css
--primary-color: #8e44ad;
--secondary-color: #9b59b6;
```

### Theme Cam
```css
--primary-color: #d35400;
--secondary-color: #e67e22;
```

## 🔧 Tùy Chỉnh Chi Tiết

### 1. Bỏ Hiệu Ứng Hover
Nếu không muốn hiệu ứng di chuột, tìm và xóa:
```css
transform: translateY(-1px);
transform: translateY(-2px);
transform: scale(1.01);
```

### 2. Thay Đổi Font chữ
Trong `body`:
```css
body {
    font-family: 'Arial', sans-serif;  /* Thay font */
}
```

### 3. Điều Chỉnh Buttons
Tìm class `.btn-*` để thay đổi:
- Padding: `padding: 8px 16px;`
- Font size: `font-size: 0.85rem;`
- Border radius: `border-radius: 8px;`

### 4. Tùy Chỉnh Tables
- Header màu: Thay đổi `.table thead`
- Row hover: Thay đổi `.table tbody tr:hover`
- Border: Thay đổi `border-bottom: 1px solid #e9ecef;`

## 📱 Responsive Design

CSS đã được tối ưu cho mobile. Các breakpoint:
- Desktop: `min-width: 991.98px`
- Mobile: `max-width: 991.98px`

## 🚀 Quick Start

1. Mở file `css/admin.css`
2. Tìm phần `:root` (dòng 8-35)
3. Thay đổi giá trị màu sắc theo ý muốn
4. Lưu file và refresh trang

## 💡 Mẹo

- Dùng công cụ Developer Tools (F12) để test màu sắc
- Sử dụng color picker để chọn màu ưng ý
- Test trên cả desktop và mobile sau khi thay đổi
- Backup file CSS gốc trước khi chỉnh sửa nhiều

## 🎨 Color Palette Tools

- [Coolors.co](https://coolors.co/) - Tạo palette màu
- [Adobe Color](https://color.adobe.com/) - Công cụ màu chuyên nghiệp
- [CSS Gradient](https://cssgradient.io/) - Tạo gradient cho buttons
