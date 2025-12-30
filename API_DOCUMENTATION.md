# Rental App API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication

### User Authentication
Most endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your_token}
```

### Admin Authentication
Admin endpoints require authentication with admin token and `admin` ability:

```
Authorization: Bearer {admin_token}
```

---

## Table of Contents
1. [User Authentication](#user-authentication)
2. [Apartments](#apartments)
3. [Bookings](#bookings)
4. [Reviews](#reviews)
5. [Admin](#admin)

---

## User Authentication

### 1. Register User

Register a new user account. User status will be set to `pending` until admin approval.

**Endpoint:** `POST /api/register`

**Authentication:** Not required

**Request Body (multipart/form-data):**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "1234567890",
  "birth_date": "1990-01-01",
  "profile_image": "file (image: jpeg,png,jpg,gif, max:2MB)",
  "id_card_image": "file (image: jpeg,png,jpg,gif, max:4MB)",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Validation Rules:**
- `first_name`: required, string, max:20, letters and Arabic characters only
- `last_name`: required, string, max:20, letters and Arabic characters only
- `phone`: required, string, unique, min:10, max:20
- `birth_date`: required, date, must be before today
- `profile_image`: required, image, mimes:jpeg,png,jpg,gif, max:2048KB
- `id_card_image`: required, image, mimes:jpeg,png,jpg,gif, max:4096KB
- `password`: required, string, min:8, confirmed

**Success Response (201):**
```json
{
  "message": "User registered successfully",
  "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "phone": "1234567890",
    "birth_date": "1990-01-01",
    "profile_image_url": "http://your-domain.com/storage/profile_images/xxx.jpg",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Error Response (500):**
```json
{
  "message": "registration failed",
  "data": "Error message"
}
```

---

### 2. Login User

Login with phone and password.

**Endpoint:** `POST /api/login`

**Authentication:** Not required

**Request Body:**
```json
{
  "phone": "1234567890",
  "password": "password123"
}
```

**Validation Rules:**
- `phone`: required, string
- `password`: required, string, min:8

**Success Response (200):**
```json
{
  "message": "Login successful",
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user_data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "phone": "1234567890",
    "birth_date": "1990-01-01",
    "profile_image_url": "http://your-domain.com/storage/profile_images/xxx.jpg",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Error Responses:**
- **401** - Invalid credentials:
```json
{
  "message": "Invalid phone or password"
}
```

- **403** - Account rejected:
```json
{
  "message": "Your account is rejected"
}
```

---

### 3. Check Phone Availability

Check if a phone number is available for registration.

**Endpoint:** `POST /api/check_phone_availability`

**Authentication:** Not required

**Request Body:**
```json
{
  "phone": "1234567890"
}
```

**Success Response (200):**
```json
{
  "message": "phone number is available"
}
```

**Error Response (409):**
```json
{
  "message": "Phone number already used"
}
```

---

### 4. Logout User

Logout and invalidate the current access token.

**Endpoint:** `POST /api/logout`

**Authentication:** Required (Bearer token)

**Success Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### 5. Get Current User

Get the authenticated user's information.

**Endpoint:** `GET /api/self`

**Authentication:** Required (Bearer token)

**Success Response (200):**
```json
{
  "message": "success",
  "user_data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "phone": "1234567890",
    "birth_date": "1990-01-01",
    "profile_image_url": "http://your-domain.com/storage/profile_images/xxx.jpg",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Error Response (401):**
```json
{
  "message": "invalid token"
}
```

---

## Apartments

### 1. Get All Apartments

Get a list of all apartments with their images.

**Endpoint:** `GET /api/apartments`

**Authentication:** Not required

**Success Response (200):**
```json
{
  "message": "get apartments success",
  "apartments": [
    {
      "id": 1,
      "address": "123 Main St",
      "description": "Beautiful apartment",
      "city": "Damascus",
      "governorate": "Damascus",
      "price": 100.00,
      "number_of_rooms": 3,
      "is_rented": false,
      "images": [
        {
          "id": 1,
          "image_url": "http://your-domain.com/storage/apartment_images/xxx.jpg"
        }
      ],
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "count": 10
}
```

---

### 2. Create Apartment

Create a new apartment listing. Only authenticated users can create apartments.

**Endpoint:** `POST /api/create_apartment`

**Authentication:** Required (Bearer token)

**Request Body (multipart/form-data):**
```json
{
  "address": "123 Main St",
  "description": "Beautiful apartment in the city center",
  "city": "Damascus",
  "governorate": "Damascus",
  "price": 100.00,
  "number_of_rooms": 3,
  "is_rented": false,
  "images": [
    "file1 (image: jpg,jpeg,png,webp, max:4MB)",
    "file2 (image: jpg,jpeg,png,webp, max:4MB)"
  ]
}
```

**Validation Rules:**
- `address`: required, string, max:255
- `description`: nullable, string, max:1000
- `city`: required, string, max:100
- `governorate`: required, string, max:100
- `price`: required, numeric, min:0
- `number_of_rooms`: required, numeric, min:1
- `is_rented`: optional, boolean (default: false)
- `images`: required, array, min:1 image
- `images.*`: image, mimes:jpg,jpeg,png,webp, max:4096KB

**Success Response (200):**
```json
{
  "message": "Apartment created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "address": "123 Main St",
    "description": "Beautiful apartment in the city center",
    "city": "Damascus",
    "governorate": "Damascus",
    "price": 100.00,
    "number_of_rooms": 3,
    "is_rented": false,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Error Response (500):**
```json
{
  "status": "error",
  "message": "Apartment creation failed",
  "error": "Error message"
}
```

---

### 3. Search Apartments

Search apartments with filters. Returns paginated results (10 per page).

**Endpoint:** `GET /api/apartments`

**Authentication:** Required (Bearer token)

**Query Parameters:**
- `governorate` (optional): Filter by governorate
- `city` (optional): Filter by city
- `min_price` (optional): Minimum price
- `max_price` (optional): Maximum price
- `min_rooms` (optional): Minimum number of rooms
- `max_rooms` (optional): Maximum number of rooms
- `page` (optional): Page number for pagination

**Example Request:**
```
GET /api/apartments?governorate=Damascus&min_price=50&max_price=200&min_rooms=2&page=1
```

**Success Response (200):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "address": "123 Main St",
      "description": "Beautiful apartment",
      "city": "Damascus",
      "governorate": "Damascus",
      "price": 100.00,
      "number_of_rooms": 3,
      "is_rented": false,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "first_page_url": "http://your-domain.com/api/apartments?page=1",
  "from": 1,
  "last_page": 5,
  "last_page_url": "http://your-domain.com/api/apartments?page=5",
  "next_page_url": "http://your-domain.com/api/apartments?page=2",
  "path": "http://your-domain.com/api/apartments",
  "per_page": 10,
  "prev_page_url": null,
  "to": 10,
  "total": 50
}
```

---

### 4. Get User Apartments

Get all apartments owned by a specific user.

**Endpoint:** `GET /api/user_apartments`

**Authentication:** Not required

**Query Parameters:**
- `user_id` (required): The ID of the user

**Example Request:**
```
GET /api/user_apartments?user_id=1
```

**Success Response (200):**
```json
{
  "message": "User apartments retrieved successfully",
  "apartments": [
    {
      "id": 1,
      "address": "123 Main St",
      "description": "Beautiful apartment",
      "city": "Damascus",
      "governorate": "Damascus",
      "price": 100.00,
      "number_of_rooms": 3,
      "is_rented": false,
      "images": [
        {
          "id": 1,
          "image_url": "http://your-domain.com/storage/apartment_images/xxx.jpg"
        }
      ],
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "count": 5
}
```

---

### 5. Get Apartment Bookings

Get all future bookings for a specific apartment. Only the apartment owner can access this.

**Endpoint:** `GET /api/apartment_bookings/{id}`

**Authentication:** Required (Bearer token)

**URL Parameters:**
- `id`: Apartment ID

**Success Response (200):**
```json
{
  "message": "Bookings retrieved successfully",
  "bookings": [
    {
      "id": 1,
      "user_id": 2,
      "apartment_id": 1,
      "start_date": "2025-02-01",
      "end_date": "2025-02-05",
      "status": "approved",
      "total_price": "400.00",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

**Error Responses:**
- **403** - Not the owner:
```json
{
  "message": "You do not own this apartment!"
}
```

- **404** - No bookings found:
```json
{
  "message": "No bookings found!"
}
```

---

## Bookings

### 1. Create Booking

Create a new booking request for an apartment. User must have `approved` status.

**Endpoint:** `POST /api/bookings`

**Authentication:** Required (Bearer token)

**Request Body:**
```json
{
  "apartment_id": 1,
  "start_date": "2025-02-01",
  "end_date": "2025-02-05"
}
```

**Validation Rules:**
- `apartment_id`: required, integer, must exist in apartments table
- `start_date`: required, date, must be today or later
- `end_date`: required, date, must be after start_date

**Success Response (201):**
```json
{
  "message": "Booking request created successfully. Waiting for apartment owner approval.",
  "booking": {
    "id": 1,
    "apartment_id": 1,
    "start_date": "2025-02-01",
    "end_date": "2025-02-05",
    "status": "pending",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Error Responses:**
- **403** - Account not approved:
```json
{
  "message": "Your account must be approved before you can make bookings. Current status: pending"
}
```

- **403** - Cannot book own apartment:
```json
{
  "message": "You cannot book your own apartment"
}
```

- **409** - Date conflict:
```json
{
  "message": "Booking conflict: The apartment is already booked for the selected dates!",
  "conflicting_booking": {
    "id": 2,
    "start_date": "2025-02-03",
    "end_date": "2025-02-07",
    "status": "approved"
  }
}
```

---

### 2. Approve Booking

Approve a pending booking. Only the apartment owner can approve bookings.

**Endpoint:** `POST /api/bookings/{id}/approve`

**Authentication:** Required (Bearer token)

**URL Parameters:**
- `id`: Booking ID

**Success Response (200):**
```json
{
  "message": "Booking approved successfully",
  "booking": {
    "id": 1,
    "apartment_id": 1,
    "start_date": "2025-02-01",
    "end_date": "2025-02-05",
    "status": "approved",
    "total_price": "400.00"
  }
}
```

**Error Responses:**
- **403** - Not the owner:
```json
{
  "message": "you do not own this apartment!"
}
```

- **400** - Not pending:
```json
{
  "message": "Only pending bookings can be approved"
}
```

- **409** - Date conflict:
```json
{
  "message": "Cannot approve approvedBooking. Dates conflict with an existing approved booking.",
  "conflicting_approvedBooking": {
    "id": 2,
    "start_date": "2025-02-03",
    "end_date": "2025-02-07"
  }
}
```

---

### 3. Reject Booking

Reject a pending booking. Only the apartment owner can reject bookings.

**Endpoint:** `POST /api/bookings/{id}/reject`

**Authentication:** Required (Bearer token)

**URL Parameters:**
- `id`: Booking ID

**Success Response (200):**
```json
{
  "message": "Booking rejected successfully",
  "booking": {
    "id": 1,
    "status": "rejected"
  }
}
```

**Error Responses:**
- **403** - Not the owner:
```json
{
  "message": "you do not own this apartment!"
}
```

- **400** - Not pending:
```json
{
  "message": "Only pending bookings can be rejected"
}
```

---

### 4. Cancel Booking

Cancel a booking. Only the user who made the booking can cancel it. Can only cancel `pending` or `approved` bookings.

**Endpoint:** `POST /api/bookings/{id}/cancel`

**Authentication:** Required (Bearer token)

**URL Parameters:**
- `id`: Booking ID

**Success Response (200):**
```json
{
  "message": "Booking cancelled successfully",
  "booking": {
    "id": 1,
    "status": "cancelled"
  }
}
```

**Error Responses:**
- **400** - Not the booking owner:
```json
{
  "message": "you did not make this booking !"
}
```

- **400** - Cannot cancel:
```json
{
  "message": "you cannot cancel this booking"
}
```

---

### 5. Get User Bookings

Get all bookings for the authenticated user.

**Endpoint:** `GET /api/bookings/user_bookings`

**Authentication:** Required (Bearer token)

**Success Response (200):**
```json
{
  "message": "getting user bookings success",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "apartment_id": 1,
      "start_date": "2025-02-01",
      "end_date": "2025-02-05",
      "status": "approved",
      "total_price": "400.00",
      "apartment": {
        "id": 1,
        "address": "123 Main St",
        "city": "Damascus",
        "governorate": "Damascus",
        "price": 100.00
      },
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

## Reviews

### 1. Create Review

Create a review for an apartment. User must have an approved booking for the apartment and can only review once.

**Endpoint:** `POST /api/apartments/{apartment_id}/review`

**Authentication:** Required (Bearer token)

**URL Parameters:**
- `apartment_id`: Apartment ID

**Request Body:**
```json
{
  "rating": 5,
  "comment": "Great apartment, very clean and well located!"
}
```

**Validation Rules:**
- `rating`: required, integer, between:1,5
- `comment`: nullable, string

**Success Response (201):**
```json
{
  "message": "Review submitted successfully",
  "review": {
    "id": 1,
    "apartment_id": 1,
    "user_id": 1,
    "rating": 5,
    "comment": "Great apartment, very clean and well located!",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Error Responses:**
- **403** - No approved booking:
```json
{
  "message": "You can only review apartments you have rented."
}
```

- **409** - Already reviewed:
```json
{
  "message": "You have already reviewed this apartment."
}
```

---

## Admin

### 1. Admin Login

Login as an admin user.

**Endpoint:** `POST /api/login_admin`

**Authentication:** Not required

**Request Body:**
```json
{
  "phone": "1234567890",
  "password": "password123"
}
```

**Validation Rules:**
- `phone`: required, string
- `password`: required, string, min:8

**Success Response (200):**
```json
{
  "message": "Admin login success",
  "admin": {
    "id": 1,
    "first_name": "Admin",
    "last_name": "User",
    "phone": "1234567890"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

**Error Response (401):**
```json
{
  "message": "Invalid phone or password"
}
```

---

### 2. Get All Users

Get a paginated list of all users (10 per page).

**Endpoint:** `GET /api/get_All_Users`

**Authentication:** Required (Admin token with `admin` ability)

**Query Parameters:**
- `page` (optional): Page number for pagination

**Success Response (200):**
```json
{
  "message": "Get all users success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "full_name": "John Doe",
        "phone": "1234567890",
        "birth_date": "1990-01-01",
        "status": "approved",
        "profile_image_url": "http://your-domain.com/storage/profile_images/xxx.jpg"
      }
    ],
    "per_page": 10,
    "total": 50
  }
}
```

---

### 3. Get Pending Users

Get all users with `pending` status.

**Endpoint:** `GET /api/pending_users`

**Authentication:** Required (Admin token with `admin` ability)

**Success Response (200):**
```json
{
  "message": "success",
  "data": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "phone": "1234567890",
      "status": "pending"
    }
  ]
}
```

**Error Response (404):**
```json
{
  "message": "There are no pending users"
}
```

---

### 4. Get User by ID

Get detailed information about a specific user.

**Endpoint:** `GET /api/user/{id}`

**Authentication:** Required (Admin token with `admin` ability)

**URL Parameters:**
- `id`: User ID

**Success Response (200):**
```json
{
  "message": "User get successfully",
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "phone": "1234567890",
    "birth_date": "1990-01-01",
    "status": "approved",
    "profile_image": "public/profile_images/xxx.jpg",
    "id_card_image": "private/id_cards/xxx.jpg",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Error Response (404):**
```json
{
  "message": "User not found"
}
```

---

### 5. Accept User

Approve a pending user account.

**Endpoint:** `PATCH /api/accept_user/{id}`

**Authentication:** Required (Admin token with `admin` ability)

**URL Parameters:**
- `id`: User ID

**Success Response (200):**
```json
{
  "message": "User accepted successfully"
}
```

**Error Response (404):**
```json
{
  "message": "User not found"
}
```

---

### 6. Reject User

Reject a pending user account.

**Endpoint:** `PATCH /api/reject_user/{id}`

**Authentication:** Required (Admin token with `admin` ability)

**URL Parameters:**
- `id`: User ID

**Success Response (200):**
```json
{
  "message": "User rejected successfully"
}
```

**Error Response (404):**
```json
{
  "message": "User not found"
}
```

---

### 7. Delete User

Delete a user account.

**Endpoint:** `DELETE /api/delete_user/{id}`

**Authentication:** Required (Admin token with `admin` ability)

**URL Parameters:**
- `id`: User ID

**Success Response (200):**
```json
{
  "message": "user deleted success"
}
```

**Error Response (404):**
```json
{
  "message": "user not fond"
}
```

---

## Status Codes

- **200** - Success
- **201** - Created
- **400** - Bad Request
- **401** - Unauthorized
- **403** - Forbidden
- **404** - Not Found
- **409** - Conflict
- **500** - Internal Server Error

---

## User Status Values

- `pending` - Waiting for admin approval
- `approved` - Account approved, can make bookings
- `rejected` - Account rejected by admin

## Booking Status Values

- `pending` - Waiting for apartment owner approval
- `approved` - Booking approved by owner
- `rejected` - Booking rejected by owner
- `cancelled` - Booking cancelled by user

---

## Notes

1. All dates should be in `YYYY-MM-DD` format
2. All timestamps are in ISO 8601 format
3. Image URLs are automatically generated and accessible via the storage link
4. Password must be at least 8 characters
5. Phone numbers should be unique
6. Users can only review apartments they have rented (approved bookings)
7. Users can only cancel their own bookings
8. Apartment owners can only approve/reject bookings for their own apartments
9. Users with `pending` or `rejected` status cannot make bookings
10. Users cannot book their own apartments

---

## Example cURL Requests

### Register User
```bash
curl -X POST http://your-domain.com/api/register \
  -F "first_name=John" \
  -F "last_name=Doe" \
  -F "phone=1234567890" \
  -F "birth_date=1990-01-01" \
  -F "profile_image=@/path/to/image.jpg" \
  -F "id_card_image=@/path/to/id.jpg" \
  -F "password=password123" \
  -F "password_confirmation=password123"
```

### Login
```bash
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "1234567890",
    "password": "password123"
  }'
```

### Create Apartment (with token)
```bash
curl -X POST http://your-domain.com/api/create_apartment \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "address=123 Main St" \
  -F "description=Beautiful apartment" \
  -F "city=Damascus" \
  -F "governorate=Damascus" \
  -F "price=100" \
  -F "number_of_rooms=3" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
```

### Create Booking
```bash
curl -X POST http://your-domain.com/api/bookings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "apartment_id": 1,
    "start_date": "2025-02-01",
    "end_date": "2025-02-05"
  }'
```

---

**Last Updated:** January 2025

