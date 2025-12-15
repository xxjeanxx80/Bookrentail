# Admin Use Case Decomposition

## 1. Authentication Management

### 1.1 Admin Login
- **Use Case**: UC-ADM-001
- **Actor**: Administrator
- **Description**: Administrator logs into the admin panel with credentials
- **Preconditions**: Admin must have valid credentials
- **Postconditions**: Admin session is created, redirected to dashboard
- **Main Flow**:
  1. Admin navigates to `/Admin/login.php`
  2. Admin enters username and password
  3. System validates credentials against database
  4. If valid, create session and redirect to categories.php
  5. If invalid, display error message
- **Alternative Flow**: Remember Me functionality
  - If "Remember Me" is checked, store token in database and cookie
  - Auto-login on next visit if valid token exists

### 1.2 Admin Logout
- **Use Case**: UC-ADM-002
- **Actor**: Administrator
- **Description**: Administrator logs out from the admin panel
- **Preconditions**: Admin must be logged in
- **Postconditions**: Admin session is destroyed, redirected to login page

## 2. Category Management

### 2.1 View Categories
- **Use Case**: UC-ADM-003
- **Actor**: Administrator
- **Description**: View list of all book categories
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display categories table with status, edit, and delete options

### 2.2 Add Category
- **Use Case**: UC-ADM-004
- **Actor**: Administrator
- **Description**: Add a new book category
- **Preconditions**: Admin must be logged in
- **Postconditions**: New category is created and displayed in list
- **Main Flow**:
  1. Click "Add Category" button
  2. Enter category name
  3. Select status (Active/Inactive)
  4. Submit form
  5. System validates and saves category
  6. Redirect to categories list

### 2.3 Edit Category
- **Use Case**: UC-ADM-005
- **Actor**: Administrator
- **Description**: Modify existing category details
- **Preconditions**: Admin must be logged in, category must exist
- **Postconditions**: Category information is updated

### 2.4 Delete Category
- **Use Case**: UC-ADM-006
- **Actor**: Administrator
- **Description**: Remove a category from the system
- **Preconditions**: Admin must be logged in, category must exist
- **Postconditions**: Category is deleted from database
- **Business Rule**: Confirmation dialog before deletion

### 2.5 Toggle Category Status
- **Use Case**: UC-ADM-007
- **Actor**: Administrator
- **Description**: Activate or deactivate a category
- **Preconditions**: Admin must be logged in
- **Postconditions**: Category status is updated (Active/Inactive)

## 3. Book Management

### 3.1 View Books
- **Use Case**: UC-ADM-008
- **Actor**: Administrator
- **Description**: View list of all books with details
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display books table with cover, title, author, category, price, and actions

### 3.2 Add Book
- **Use Case**: UC-ADM-009
- **Actor**: Administrator
- **Description**: Add a new book to the system
- **Preconditions**: Admin must be logged in, categories must exist
- **Postconditions**: New book is created with all details
- **Main Flow**:
  1. Click "Add Book" button
  2. Fill in book details:
     - ISBN
     - Book Name
     - Author
     - Category
     - Security Charges
     - Rent Cost (per day)
     - Quantity
     - Short Description
     - Full Description
     - Book Image (required)
  3. Submit form
  4. System validates data (including duplicate book name check)
  5. Save book and upload image
  6. Redirect to books list

### 3.3 Edit Book
- **Use Case**: UC-ADM-010
- **Actor**: Administrator
- **Description**: Modify existing book details
- **Preconditions**: Admin must be logged in, book must exist
- **Postconditions**: Book information is updated
- **Special Rules**:
  - Image is optional when editing
  - If new image uploaded, replace old one
  - If no new image, keep existing image
  - Validate duplicate name (excluding current book)

### 3.4 Delete Book
- **Use Case**: UC-ADM-011
- **Actor**: Administrator
- **Description**: Remove a book from the system
- **Preconditions**: Admin must be logged in, book must exist
- **Postconditions**: Book is deleted from database
- **Business Rule**: Confirmation dialog before deletion

### 3.5 Toggle Book Status
- **Use Case**: UC-ADM-012
- **Actor**: Administrator
- **Description**: Activate or deactivate a book
- **Preconditions**: Admin must be logged in
- **Postconditions**: Book status is updated (Active/Inactive)

### 3.6 View Book Details
- **Use Case**: UC-ADM-013
- **Actor**: Administrator
- **Description**: View detailed information about a specific book
- **Preconditions**: Admin must be logged in, book must exist
- **Postconditions**: Display comprehensive book details

## 4. Order Management

### 4.1 View Orders
- **Use Case**: UC-ADM-014
- **Actor**: Administrator
- **Description**: View list of all customer orders
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display orders table with ID, date, book, price, status, and actions

### 4.2 Update Order Status
- **Use Case**: UC-ADM-015
- **Actor**: Administrator
- **Description**: Change the status of an order
- **Preconditions**: Admin must be logged in, order must exist
- **Postconditions**: Order status is updated
- **Business Rules**:
  - Available statuses: Pending, Processing, Shipped, Delivered, Cancelled, Returned
  - If order is Cancelled or Returned, book quantity is automatically increased
  - Status changes are logged with timestamp

### 4.3 View Order Details
- **Use Case**: UC-ADM-016
- **Actor**: Administrator
- **Description**: View comprehensive details of a specific order
- **Preconditions**: Admin must be logged in, order must exist
- **Postconditions**: Display order details including:
  - Customer information
  - Book details
  - Order timeline
  - Payment information
  - Status update options

## 5. User Management

### 5.1 View Users
- **Use Case**: UC-ADM-017
- **Actor**: Administrator
- **Description**: View list of all registered users
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display users table with ID, name, email, mobile, join date

### 5.2 Delete User
- **Use Case**: UC-ADM-018
- **Actor**: Administrator
- **Description**: Remove a user from the system
- **Preconditions**: Admin must be logged in, user must exist
- **Postconditions**: User account is deleted from database
- **Business Rule**: Confirmation dialog before deletion
- **Note**: This action is irreversible and affects user's order history

## 6. Feedback Management

### 6.1 View Feedbacks
- **Use Case**: UC-ADM-019
- **Actor**: Administrator
- **Description**: View all customer feedbacks and ratings
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display feedbacks table with:
  - Order ID
  - Book Name
  - Customer Name
  - Rating (1-5 stars)
  - Comment
  - Date

### 6.2 Delete Feedback
- **Use Case**: UC-ADM-020
- **Actor**: Administrator
- **Description**: Remove inappropriate or unwanted feedback
- **Preconditions**: Admin must be logged in, feedback must exist
- **Postconditions**: Feedback is deleted from database
- **Business Rule**: Confirmation dialog before deletion

## 7. Dashboard & Navigation

### 7.1 Access Dashboard
- **Use Case**: UC-ADM-021
- **Actor**: Administrator
- **Description**: Navigate to admin dashboard/home
- **Preconditions**: Admin must be logged in
- **Postconditions**: Redirected to categories page (main admin page)

### 7.2 Navigate Between Sections
- **Use Case**: UC-ADM-022
- **Actor**: Administrator
- **Description**: Move between different admin sections
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display selected section
- **Available Sections**:
  - Categories
  - Books
  - Orders
  - Users
  - Feedbacks

## 8. Security Features

### 8.1 Session Management
- **Use Case**: UC-ADM-023
- **Actor**: System
- **Description**: Manage admin sessions for security
- **Preconditions**: Admin is logged in
- **Postconditions**: Session is maintained or expired as needed
- **Features**:
  - Session validation on each page
  - Auto-redirect to login if session invalid
  - Remember Me token support

### 8.2 Input Validation & Sanitization
- **Use Case**: UC-ADM-024
- **Actor**: System
- **Description**: Validate and sanitize all admin inputs
- **Preconditions**: Data is submitted through admin forms
- **Postconditions**: Data is clean and safe for database
- **Methods**:
  - mysqli_real_escape_string for text inputs
  - Type casting for numeric values
  - HTML special characters encoding for display

## 9. Error Handling

### 9.1 Handle Database Errors
- **Use Case**: UC-ADM-025
- **Actor**: System
- **Description**: Manage database operation failures
- **Preconditions**: Database operation fails
- **Postconditions**: Error message is displayed to admin
- **Examples**:
  - Connection failures
  - Query execution errors
  - Constraint violations

### 9.2 Handle File Upload Errors
- **Use Case**: UC-ADM-026
- **Actor**: System
- **Description**: Manage book image upload failures
- **Preconditions**: Image upload fails
- **Postconditions**: Error message displayed, form data preserved
- **Error Types**:
  - Invalid file format
  - File size exceeded
  - Upload directory permissions

## 10. Reporting & Analytics (Future Enhancements)

### 10.1 Sales Report
- **Use Case**: UC-ADM-027
- **Actor**: Administrator
- **Description**: Generate sales reports for specific periods
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display sales statistics and charts

### 10.2 Inventory Report
- **Use Case**: UC-ADM-028
- **Actor**: Administrator
- **Description**: View inventory status and low stock alerts
- **Preconditions**: Admin must be logged in
- **Postconditions**: Display inventory levels and recommendations

---

## Non-Functional Requirements

### Performance
- Page load time under 3 seconds
- Efficient database queries with proper indexing
- Image optimization for fast loading

### Security
- All admin pages require authentication
- SQL injection prevention through prepared statements
- XSS prevention through output encoding
- CSRF protection on form submissions

### Usability
- Responsive design for mobile and desktop
- Intuitive navigation structure
- Clear confirmation dialogs for destructive actions
- Consistent UI/UX across all admin pages

### Reliability
- Graceful error handling
- Data validation before database operations
- Transaction management for critical operations
