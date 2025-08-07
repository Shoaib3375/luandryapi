# e-Laundry API - Architecture Documentation

## Directory Structure & Interconnections

### **Controllers** (`app/Http/Controllers/API/`)
**Purpose**: Handle HTTP requests and coordinate business logic
- Uses **Traits** for consistent API responses
- Injects **Services** for business logic
- Returns **Resources** for formatted responses
- Validates requests and delegates to services

**Example Flow**:
```
AuthController → ApiResponseTrait → UserResource
LaundryOrderController → OrderService → LaundryOrderResource
```

### **DTOs** (`app/DTOs/`)
**Purpose**: Data Transfer Objects for type-safe data passing
- `CreateOrderDTO`: Structures order creation data
- `CouponDiscountDTO`: Handles coupon calculation results
- Used by **Services** and **Repositories** for clean data transfer

### **Events** (`app/Events/`)
**Purpose**: Trigger actions when specific events occur
- `OrderStatusUpdated`: Fired when order status changes
- Implements `ShouldBroadcast` for real-time updates
- Listened to by **Listeners**

### **Listeners** (`app/Listeners/`)
**Purpose**: Handle events and perform side effects
- `SendOrderStatusNotification`: Sends notifications when order status changes
- `LogNotificationSent`: Logs notification activities
- Registered in **EventServiceProvider**

### **Notifications** (`app/Notifications/`)
**Purpose**: Send notifications via multiple channels
- `OrderStatusChangedNotification`: Email/database/broadcast notifications
- Used by **Listeners** and **Services**
- Supports mail, database, and broadcast channels

### **Providers** (`app/Providers/`)
**Purpose**: Register services and bind interfaces
- `AppServiceProvider`: Binds interfaces to implementations
- `EventServiceProvider`: Maps events to listeners
- Enables dependency injection throughout the app

### **Repositories** (`app/Repositories/`)
**Purpose**: Data access layer abstraction
- Implements **Contracts** (interfaces)
- Used by **Services** for database operations
- Integrates **Redis Cache** for performance optimization
- `OrderRepository`, `ServiceRepository`, `CouponRepository`

### **Services** (`app/Services/`)
**Purpose**: Business logic layer
- `OrderService`: Core order management logic
- `PriceCalculationService`: Handles pricing logic with cache
- `NotificationService`: Manages notifications
- Uses **Repositories**, **DTOs**, **Redis Cache**, and **Validators**

### **Traits** (`app/Traits/`)
**Purpose**: Reusable code snippets
- `ApiResponseTrait`: Standardized API responses
- Used by **Controllers** for consistent response format

### **Resources** (`app/Http/Resources/`)
**Purpose**: Transform models into JSON responses
- `LaundryOrderResource`: Formats order data
- `UserResource`: Formats user data
- `ServiceResource`: Formats service data
- Used by **Controllers** for API responses

### **Contracts** (`app/Contracts/`)
**Purpose**: Define interfaces for dependency injection
- `OrderRepositoryInterface`: Repository contract
- `NotificationServiceInterface`: Notification service contract
- Implemented by concrete classes, bound in **Providers**

### **Enums** (`app/Enums/`)
**Purpose**: Type-safe constants
- `OrderStatus`: Order status values
- `PaymentStatus`: Payment status values
- `PricingMethod`: Service pricing methods
- Used throughout the application for consistency

## **Data Flow Example**

```
1. Controller receives request
   ↓
2. Controller validates & uses DTO
   ↓
3. Controller calls Service
   ↓
4. Service checks Redis Cache
   ↓
5. Service uses Repository (via Contract) if cache miss
   ↓
6. Service caches result in Redis
   ↓
7. Service fires Event
   ↓
8. Listener handles Event
   ↓
9. Listener sends Notification
   ↓
10. Controller returns Resource response
```

## **Key Interconnections**

- **Controllers** → **Services** → **Redis Cache** → **Repositories** → **Models**
- **Events** → **Listeners** → **Notifications**
- **Providers** bind **Contracts** to implementations
- **DTOs** carry data between layers
- **Resources** format output
- **Traits** provide reusable functionality
- **Enums** ensure type safety across layers
- **Redis Cache** accelerates data access across all layers

This architecture follows SOLID principles with clear separation of concerns, dependency injection, and event-driven design for scalable laundry service management.
