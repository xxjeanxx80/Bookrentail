# 🎨 Customer CSS Configuration Guide

## 📋 Mục Lục
- [CSS Variables](#css-variables)
- [Cấu trúc file](#cấu-trúc-file)
- [Cách thay đổi màu sắc](#cách-thay-đổi-màu-sắc)
- [Cách thay đổi kích thước](#cách-thay-đổi-kích-thước)
- [Responsive](#responsive)

---

## 🎯 CSS Variables

File `customer.css` sử dụng CSS variables để dễ dàng tùy chỉnh. Tất cả variables được đặt ở đầu file trong phần `:root`.

### 🔥 Các biến quan trọng nhất:

#### **MÀU SẮC**
```css
:root {
    /* Màu chính - Thay đổi để đổi toàn bộ giao diện */
    --primary-color: rgb(9, 105, 218);       /* Xanh dương */
    --primary-hover: rgb(8, 97, 199);        /* Xanh khi hover */
    
    /* Màu phụ - Nút quan trọng */
    --secondary-color: #f00b52;              /* Đỏ */
    --secondary-hover: #b20909;              /* Đỏ khi hover */
    
    /* Màu nền */
    --bg-light: #f8f9fa;                     /* Nền sáng */
    --bg-white: #ffffff;                     /* Nền trắng */
    --bg-dark: #404040;                      /* Nền tối */
    
    /* Màu chữ */
    --text-dark: #000000;                    /* Chữ đen */
    --text-light: #ffffff;                   /* Chữ trắng */
    --text-muted: rgb(212, 214, 216);        /* Chữ xám */
}
```

#### **KÍCH THƯỚC**
```css
:root {
    /* Carousel */
    --carousel-height: 29rem;                /* Chiều cao slider */
    --carousel-height-mobile: 26rem;         /* Chiều cao mobile */
    
    /* Nút */
    --rent-btn-width: 60%;                   /* Chiều rộng nút rent */
    --rent-btn-radius: 200px;                /* Bo góc nút */
    
    /* Font */
    --carousel-font-large: 25px;             /* Font lớn */
    --footer-font-size: 14px;                /* Font footer */
}
```

---

## 📁 Cấu trúc file

```
customer.css
├── 1. CSS VARIABLES     // Tất cả biến tùy chỉnh
├── 2. BODY & LAYOUT     // Layout chung
├── 3. NAVBAR           // Menu điều hướng
├── 4. CAROUSEL         // Slider hình ảnh
├── 5. BOOK CARDS       // Thẻ sách
├── 6. FEEDBACKS        // Đánh giá khách hàng
├── 7. FORMS            // Form đăng nhập/đăng ký
├── 8. BUTTONS          // Các nút bấm
├── 9. FOOTER           // Chân trang
├── 10. SCROLL TO TOP   // Nút cuộn lên
└── 11. RESPONSIVE      // Mobile/Tablet
```

---

## 🎨 Cách thay đổi màu sắc

### **1. Đổi màu chủ đạo**
```css
:root {
    --primary-color: #ff6b6b;        /* Đổi sang đỏ coral */
    --primary-hover: #ff5252;         /* Đỏ coral khi hover */
}
```

### **2. Đổi màu nút quan trọng**
```css
:root {
    --secondary-color: #28a745;      /* Đổi sang xanh lá */
    --secondary-hover: #218838;       /* Xanh lá khi hover */
}
```

### **3. Đổi theme Dark Mode**
```css
:root {
    --bg-light: #1a1a1a;              /* Nền tối */
    --text-dark: #ffffff;             /* Chữ trắng */
    --text-muted: #cccccc;            /* Chữ xám nhạt */
}
```

---

## 📏 Cách thay đổi kích thước

### **1. Tăng chiều cao Carousel**
```css
:root {
    --carousel-height: 35rem;         /* Tăng từ 29rem */
    --carousel-height-mobile: 30rem;  /* Tăng mobile */
}
```

### **2. Thay đổi nút Rent**
```css
:root {
    --rent-btn-width: 80%;            /* Rộng hơn */
    --rent-btn-radius: 10px;          /* Bo góc vuông hơn */
}
```

### **3. Thay đổi font size**
```css
:root {
    --carousel-font-large: 30px;      /* Font lớn hơn */
    --footer-font-size: 16px;         /* Font footer lớn hơn */
}
```

---

## 📱 Responsive

CSS đã được tối ưu cho mọi thiết bị:

- **Desktop (> 768px):** Full features
- **Tablet (≤ 768px):** Carousel nhỏ hơn
- **Mobile (≤ 576px):** Font nhỏ, nút compact
- **Small Mobile (≤ 480px):** Single column

### **Custom breakpoint**
```css
/* Custom cho tablet nhỏ */
@media (max-width: 600px) {
    .product {
        margin-bottom: 0.5rem;
    }
}
```

---

## 🚀 Quick Start

### **1. Theme Blue (Mặc định)**
```css
:root {
    --primary-color: rgb(9, 105, 218);
    --secondary-color: #f00b52;
}
```

### **2. Theme Green**
```css
:root {
    --primary-color: #28a745;
    --secondary-color: #dc3545;
}
```

### **3. Theme Purple**
```css
:root {
    --primary-color: #6f42c1;
    --secondary-color: #fd7e14;
}
```

### **4. Theme Dark**
```css
:root {
    --primary-color: #007bff;
    --bg-light: #2c3e50;
    --text-dark: #ecf0f1;
    --text-muted: #bdc3c7;
}
```

---

## 💡 Tips

1. **Luôn test trên mobile** sau khi thay đổi
2. **Sử dụng browser dev tools** để preview changes
3. **Backup file gốc** trước khi edit lớn
4. **Sử dụng semantic colors** - primary cho main actions, secondary cho important actions
5. **Maintain contrast** - đảm bảo text readable trên background

---

## 🔧 File Structure

```
assets/css/
├── customer.css     // File chính (dùng cho customer)
├── admin.css        // Admin panel (separate)
└── README.md        // File này
```

**File sử dụng:** `includes/header.php` load `customer.css` cho tất cả customer pages.

---

*Created for Bookrentail Project - Easy CSS Configuration* 🎉
