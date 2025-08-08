# 🧺 e-Laundry Order Service Workflow

## 📋 Complete Order Journey: Start to End

### 🔄 Order Lifecycle Overview

```mermaid
graph TD
    A[Customer Registration/Login] --> B[Browse Services]
    B --> C[Select Service & Quantity]
    C --> D[Place Order]
    D --> E[Order Created - PENDING]
    E --> F[Admin Reviews Order]
    F --> G[Admin Updates to PROCESSING]
    G --> H[Laundry Work Begins]
    H --> I[Admin Updates to COMPLETED]
    I --> J[Customer Notification]
    J --> K[Order Delivered]
    
    F --> L[Admin Cancels - CANCELLED]
    L --> M[Cancellation Notification]
```

---

## 🏗️ System Architecture & Connectivity

### 1. **Authentication Flow**
```mermaid
sequenceDiagram
    participant C as Customer
    participant AC as AuthController
    participant JWT as JWT Service
    participant DB as Database
    
    C->>AC: POST /api/register
    AC->>DB: Create User
    DB-->>AC: User Created
    AC->>JWT: Generate Token
    JWT-->>AC: JWT Token
    AC-->>C: Token Response
    
    C->>AC: POST /api/login
    AC->>DB: Validate Credentials
    DB-->>AC: User Data
    AC->>JWT: Generate Token
    JWT-->>AC: JWT Token
    AC-->>C: Login Success + Token
```

### 2. **Order Creation Flow**
```mermaid
sequenceDiagram
    participant C as Customer
    participant OC as OrderController
    participant OS as OrderService
    participant OR as OrderRepository
    participant PC as PriceCalculation
    participant RC as Redis Cache
    participant DB as Database
    participant E as Events
    
    C->>OC: POST /api/orders
    OC->>OS: createOrder(CreateOrderDTO)
    OS->>RC: Check Service Cache
    RC-->>OS: Cache Miss
    OS->>OR: findService(serviceId)
    OR->>DB: Query Service
    DB-->>OR: Service Data
    OR->>RC: Cache Service Data
    OR-->>OS: Service Details
    OS->>PC: calculatePrice(service, quantity)
    PC-->>OS: Total Price
    OS->>OR: createOrder(orderData)
    OR->>DB: Insert Order
    DB-->>OR: Order Created
    OR->>RC: Clear User Orders Cache
    OR-->>OS: Order Model
    OS->>E: OrderCreated Event
    OS-->>OC: Order Response
    OC-->>C: Order Created (201)
```

### 3. **Order Status Update Flow**
```mermaid
sequenceDiagram
    participant A as Admin
    participant OC as OrderController
    participant OS as OrderService
    participant OR as OrderRepository
    participant RC as Redis Cache
    participant E as Events
    participant L as Listeners
    participant N as Notifications
    participant C as Customer
    
    A->>OC: PUT /api/orders/{id}/status
    OC->>OS: updateOrderStatus(orderId, status)
    OS->>RC: Check Order Cache
    RC-->>OS: Cache Hit/Miss
    OS->>OR: findOrder(orderId)
    OR-->>OS: Order Data
    OS->>OR: updateStatus(order, newStatus)
    OR->>RC: Clear Order Cache
    OR->>RC: Clear User Orders Cache
    OR-->>OS: Updated Order
    OS->>E: OrderStatusUpdated Event
    E->>L: SendOrderStatusNotification
    L->>N: OrderStatusChangedNotification
    N->>C: Email/SMS/Push Notification
    OS-->>OC: Success Response
    OC-->>A: Status Updated (200)
```

---

## 🔗 Component Connectivity Map

### **Core Components Interaction**
```mermaid
graph LR
    subgraph "API Layer"
        AC[AuthController]
        OC[OrderController]
        SC[ServiceController]
    end
    
    subgraph "Business Logic"
        OS[OrderService]
        PS[PriceCalculationService]
        NS[NotificationService]
    end
    
    subgraph "Data Layer"
        OR[OrderRepository]
        SR[ServiceRepository]
        UR[UserRepository]
    end
    
    subgraph "Events & Notifications"
        OSU[OrderStatusUpdated]
        SOSN[SendOrderStatusNotification]
        OSCN[OrderStatusChangedNotification]
    end
    
    subgraph "Database"
        DB[(MySQL/PostgreSQL)]
    end
    
    AC --> OS
    OC --> OS
    SC --> SR
    OS --> OR
    OS --> PS
    OS --> OSU
    OSU --> SOSN
    SOSN --> NS
    NS --> OSCN
    OR --> DB
    SR --> DB
    UR --> DB
```

---

## 📊 Data Flow Architecture

### **Request-Response Cycle**
```mermaid
flowchart TD
    A[HTTP Request] --> B[Route Middleware]
    B --> C[JWT Authentication]
    C --> D[Controller Method]
    D --> E[Request Validation]
    E --> F[Service Layer]
    F --> G[Repository Layer]
    G --> H[Database Query]
    H --> I[Model Response]
    I --> J[Business Logic Processing]
    J --> K[Event Triggering]
    K --> L[Resource Transformation]
    L --> M[JSON Response]
    
    K --> N[Background Jobs]
    N --> O[Notifications]
    O --> P[Email/SMS/Push]
```

---

## 🎯 Order Status State Machine

```mermaid
stateDiagram-v2
    [*] --> Pending: Order Created
    Pending --> Processing: Admin Accepts
    Pending --> Cancelled: Admin/Customer Cancels
    Processing --> Completed: Work Finished
    Processing --> Cancelled: Emergency Cancel
    Completed --> [*]: Order Delivered
    Cancelled --> [*]: Process Ended
    
    note right of Pending: Customer places order\nPayment pending
    note right of Processing: Laundry work in progress\nItems being cleaned
    note right of Completed: Ready for pickup/delivery
    note right of Cancelled: Order terminated
```

---

## 🔧 Technical Implementation Details

### **Key Endpoints & Flow**

| Endpoint                  | Method | Flow                          | Status Changes                     |
|---------------------------|--------|-------------------------------|------------------------------------|
| `/api/register`           | POST   | User Registration → JWT Token | -                                  |
| `/api/login`              | POST   | Authentication → JWT Token    | -                                  |
| `/api/services`           | GET    | Fetch Available Services      | -                                  |
| `/api/orders`             | POST   | Create Order → PENDING        | `null → PENDING`                   |
| `/api/orders`             | GET    | List User Orders              | -                                  |
| `/api/orders/{id}`        | GET    | Order Details                 | -                                  |
| `/api/orders/{id}/status` | PUT    | Admin Status Update           | `PENDING → PROCESSING → COMPLETED` |

### **Database Relationships**
```mermaid
erDiagram
    USERS ||--o{ ORDERS : places
    SERVICES ||--o{ ORDERS : includes
    ORDERS ||--o{ ORDER_STATUS_LOGS : tracks
    USERS ||--o{ ORDER_STATUS_LOGS : updates
    
    USERS {
        id bigint PK
        name string
        email string
        password string
        role enum
        created_at timestamp
    }
    
    SERVICES {
        id bigint PK
        name string
        category string
        price decimal
        pricing_method enum
        created_at timestamp
    }
    
    ORDERS {
        id bigint PK
        user_id bigint FK
        service_id bigint FK
        quantity int
        total_price decimal
        status enum
        created_at timestamp
    }
    
    ORDER_STATUS_LOGS {
        id bigint PK
        order_id bigint FK
        updated_by bigint FK
        old_status enum
        new_status enum
        created_at timestamp
    }
```

---

## 🚀 Quick Start Integration

### **1. Customer Journey**
```bash
# Register
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}

# Login
POST /api/login
{
  "email": "john@example.com",
  "password": "password123"
}

# Browse Services
GET /api/services

# Place Order
POST /api/orders
{
  "service_id": 1,
  "quantity": 5
}
```

### **2. Admin Workflow**
```bash
# Update Order Status
PUT /api/orders/1/status
{
  "status": "processing"
}

# Complete Order
PUT /api/orders/1/status
{
  "status": "completed"
}
```

---

## 📈 Performance & Scalability

### **Optimization Points**
- **Redis Caching**: Service data, order lists, user sessions, price calculations
- **Queue Jobs**: Email notifications, status updates
- **Database Indexing**: Order status, user_id, created_at
- **API Rate Limiting**: Prevent abuse
- **Event Broadcasting**: Real-time status updates
- **Cache Invalidation**: Smart cache clearing on data updates

### **Monitoring & Logging**
- Order creation/completion rates
- Status change frequency
- Notification delivery success
- API response times
- Database query performance

---

This workflow ensures seamless order management from customer registration to order completion, with robust admin controls and real-time notifications throughout the process.
