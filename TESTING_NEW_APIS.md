# Testing Guide for New APIs

## Prerequisites

1. **Get your authentication token** (if not already logged in):
   ```bash
   # Login first
   curl -X POST http://localhost/api/login \
     -H "Content-Type: application/json" \
     -d '{
       "phone": "1234567890",
       "password": "password123"
     }'
   ```
   
   Copy the `token` from the response and use it as `YOUR_TOKEN` in the examples below.

2. **Base URL**: Replace `http://localhost/api` with your actual API base URL

---

## 1. Update Apartment

**Endpoint:** `PUT /api/apartments/{id}`

### Using cURL:
```bash
curl -X PUT http://localhost/api/apartments/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Apartment Title",
    "address": "456 New Street",
    "description": "Updated description",
    "governorate": "Damascus",
    "city": "Damascus",
    "number_of_rooms": 4,
    "area": 120.50,
    "price": 150.00,
    "is_rented": false
  }'
```

### Using Postman:
1. Method: **PUT**
2. URL: `http://localhost/api/apartments/1`
3. Headers:
   - `Authorization: Bearer YOUR_TOKEN`
   - `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "title": "Updated Apartment Title",
  "address": "456 New Street",
  "description": "Updated description",
  "governorate": "Damascus",
  "city": "Damascus",
  "number_of_rooms": 4,
  "area": 120.50,
  "price": 150.00,
  "is_rented": false
}
```

### Using JavaScript (fetch):
```javascript
const updateApartment = async (apartmentId, data) => {
  const response = await fetch(`http://localhost/api/apartments/${apartmentId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer YOUR_TOKEN`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      title: "Updated Apartment Title",
      address: "456 New Street",
      description: "Updated description",
      governorate: "Damascus",
      city: "Damascus",
      number_of_rooms: 4,
      area: 120.50,
      price: 150.00,
      is_rented: false
    })
  });
  
  const result = await response.json();
  console.log(result);
};
```

### Expected Success Response (200):
```json
{
  "message": "Apartment updated successfully",
  "apartment": {
    "id": 1,
    "title": "Updated Apartment Title",
    "address": "456 New Street",
    ...
  }
}
```

### Expected Error Responses:
- **400**: You don't own this apartment
- **401**: Unauthorized (invalid/missing token)
- **404**: Apartment not found

---

## 2. Add Images to Apartment

**Endpoint:** `POST /api/apartments/{id}/images`

### Using cURL:
```bash
curl -X POST http://localhost/api/apartments/1/images \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "images[]=@/path/to/image3.jpg"
```

**Note:** On Windows PowerShell, use:
```powershell
curl -X POST http://localhost/api/apartments/1/images `
  -H "Authorization: Bearer YOUR_TOKEN" `
  -F "images[]=@C:\path\to\image1.jpg" `
  -F "images[]=@C:\path\to\image2.jpg"
```

### Using Postman:
1. Method: **POST**
2. URL: `http://localhost/api/apartments/1/images`
3. Headers:
   - `Authorization: Bearer YOUR_TOKEN`
   - **Don't set Content-Type** (Postman will set it automatically for multipart/form-data)
4. Body: Select **form-data**
   - Key: `images[]` (type: File)
   - Value: Select your image file
   - Click **+** to add more images (all with key `images[]`)

### Using JavaScript (FormData):
```javascript
const addImages = async (apartmentId, imageFiles) => {
  const formData = new FormData();
  
  // Add multiple images
  imageFiles.forEach(file => {
    formData.append('images[]', file);
  });
  
  const response = await fetch(`http://localhost/api/apartments/${apartmentId}/images`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer YOUR_TOKEN`
      // Don't set Content-Type, browser will set it with boundary
    },
    body: formData
  });
  
  const result = await response.json();
  console.log(result);
};

// Usage with file input
const fileInput = document.querySelector('input[type="file"]');
fileInput.addEventListener('change', (e) => {
  const files = Array.from(e.target.files);
  addImages(1, files);
});
```

### Expected Success Response (200):
```json
{
  "message": "Images added successfully",
  "images": [
    {
      "id": 5,
      "apartment_id": 1,
      "image_path": "apartment_images/xxx.jpg",
      "created_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Expected Error Responses:
- **400**: You don't own this apartment
- **401**: Unauthorized
- **404**: Apartment not found
- **500**: Image upload failed

---

## 3. Delete Images from Apartment

**Endpoint:** `DELETE /api/apartments/{id}/images`

### Using cURL:
```bash
curl -X DELETE http://localhost/api/apartments/1/images \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "image_ids": [1, 2, 3]
  }'
```

### Using Postman:
1. Method: **DELETE**
2. URL: `http://localhost/api/apartments/1/images`
3. Headers:
   - `Authorization: Bearer YOUR_TOKEN`
   - `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "image_ids": [1, 2, 3]
}
```

### Using JavaScript (fetch):
```javascript
const deleteImages = async (apartmentId, imageIds) => {
  const response = await fetch(`http://localhost/api/apartments/${apartmentId}/images`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer YOUR_TOKEN`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      image_ids: [1, 2, 3] // Array of image IDs to delete
    })
  });
  
  const result = await response.json();
  console.log(result);
};
```

### Expected Success Response (200):
```json
{
  "message": "Images deleted successfully",
  "deleted_count": 3
}
```

### Expected Error Responses:
- **400**: You don't own this apartment
- **401**: Unauthorized
- **404**: Apartment or images not found

---

## 4. Update/Modify Booking

**Endpoint:** `PUT /api/bookings/{id}/update`

### Using cURL:
```bash
curl -X PUT http://localhost/api/bookings/1/update \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-02-15",
    "end_date": "2025-02-20"
  }'
```

### Using Postman:
1. Method: **PUT**
2. URL: `http://localhost/api/bookings/1/update`
3. Headers:
   - `Authorization: Bearer YOUR_TOKEN`
   - `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "start_date": "2025-02-15",
  "end_date": "2025-02-20"
}
```

### Using JavaScript (fetch):
```javascript
const updateBooking = async (bookingId, startDate, endDate) => {
  const response = await fetch(`http://localhost/api/bookings/${bookingId}/update`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer YOUR_TOKEN`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      start_date: "2025-02-15",
      end_date: "2025-02-20"
    })
  });
  
  const result = await response.json();
  console.log(result);
};
```

### Expected Success Response (200):
```json
{
  "message": "Booking updated successfully",
  "booking": {
    "id": 1,
    "start_date": "2025-02-15",
    "end_date": "2025-02-20",
    "status": "pending",
    "total_price": 750.00
  }
}
```

**Note:** After updating, the booking status becomes `pending` and requires owner approval.

### Expected Error Responses:
- **400**: 
  - You didn't make this booking
  - Only pending or approved bookings can be updated
  - Dates conflict with existing approved booking
- **401**: Unauthorized
- **404**: Booking not found

---

## Quick Test Checklist

### Test Update Apartment:
- [ ] Update with all fields
- [ ] Update with only some fields (partial update)
- [ ] Try updating apartment you don't own (should fail)
- [ ] Try without authentication (should fail)

### Test Add Images:
- [ ] Add single image
- [ ] Add multiple images at once
- [ ] Try adding images to apartment you don't own (should fail)
- [ ] Try with invalid file type (should fail validation)

### Test Delete Images:
- [ ] Delete single image
- [ ] Delete multiple images
- [ ] Try deleting images from apartment you don't own (should fail)
- [ ] Try with non-existent image IDs (should return 404)

### Test Update Booking:
- [ ] Update pending booking
- [ ] Update approved booking
- [ ] Try updating cancelled/rejected booking (should fail)
- [ ] Try updating booking with conflicting dates (should fail)
- [ ] Try updating booking you didn't make (should fail)

---

## Testing Tips

1. **Use Postman Collection**: Create a Postman collection to save all these requests for easy testing

2. **Test Authentication First**: Always test login and get a valid token before testing protected endpoints

3. **Check Response Status Codes**: 
   - 200 = Success
   - 201 = Created
   - 400 = Bad Request (validation errors)
   - 401 = Unauthorized (missing/invalid token)
   - 403 = Forbidden (don't have permission)
   - 404 = Not Found
   - 409 = Conflict (e.g., booking conflicts)
   - 500 = Server Error

4. **Use Environment Variables**: In Postman, create an environment with:
   - `base_url`: `http://localhost/api`
   - `token`: Your auth token
   - Then use `{{base_url}}` and `{{token}}` in requests

5. **Test Edge Cases**:
   - Empty requests
   - Invalid data types
   - Missing required fields
   - Very large files (for images)
   - Past dates (for bookings)

---

## Example Complete Flow

```bash
# 1. Login
TOKEN=$(curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"1234567890","password":"password123"}' \
  | jq -r '.token')

# 2. Create apartment
APARTMENT_ID=$(curl -X POST http://localhost/api/create_apartment \
  -H "Authorization: Bearer $TOKEN" \
  -F "title=Test Apartment" \
  -F "address=123 Test St" \
  -F "governorate=Damascus" \
  -F "city=Damascus" \
  -F "number_of_rooms=2" \
  -F "area=80" \
  -F "price=100" \
  -F "images[]=@image.jpg" \
  | jq -r '.data.id')

# 3. Update apartment
curl -X PUT http://localhost/api/apartments/$APARTMENT_ID \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"price":150}'

# 4. Add more images
curl -X POST http://localhost/api/apartments/$APARTMENT_ID/images \
  -H "Authorization: Bearer $TOKEN" \
  -F "images[]=@image2.jpg" \
  -F "images[]=@image3.jpg"

# 5. Delete an image (assuming image ID is 1)
curl -X DELETE http://localhost/api/apartments/$APARTMENT_ID/images \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"image_ids":[1]}'
```

---

**Happy Testing! 🚀**

