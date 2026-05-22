# System Design (Conceptual)

## 1. Architecture Diagram

```mermaid
graph LR
    Client[Client (Browser)] <-->|HTTP Request/Response| WebServer[Web Server]
    WebServer <-->|Internal Call/Data| ServiceNode[Service Node / Processing Module]

    subgraph User Device
    Client
    end

    subgraph Server Side
    WebServer
    ServiceNode
    end
```

## 2. Data Flow

The data flow connects the components in the following sequence:

1.  **Request Initiation**: The **Client (Browser)** initiates an action (e.g., viewing rooms, logging in) by sending an HTTP request.
2.  **Routing & Reception**: The **Web Server** receives the request and determines the appropriate resource or script to handle it.
3.  **Processing**: The Web Server forwards the context to the **Service Node / Processing Module**. 
    *   This module executes the business logic (e.g., calculating booking costs, verifying credentials).
    *   It interacts with the data storage (database) to retrieve or update information.
4.  **Response Generation**: The Service Node returns the results to the Web Server.
5.  **Display**: The Web Server constructs the final HTML response and sends it back to the **Client** for rendering.

## 3. Component Identification

### Distributed Components
*   **Client (Browser)**: This component operates on the users' individual devices (laptops, mobile phones) and is distributed across multiple locations wherever the users are accessing the system.

### Centralized Components
*   **Web Server**: This is the central entry point for all incoming traffic, hosted in a centralized server environment (e.g., XAMPP Apache server).
*   **Service Node / Processing Module**: The core business logic and data processing occur centrally ensuring data consistency and security.

## 4. Concrete System Architecture (Hotel System)

This section details the specific implementation of the Hotel Management System.

### Application Logic Flow

```mermaid
graph TD
    User((User))
    Browser[Web Browser]
    
    subgraph "Web Server (Apache/XAMPP)"
        Auth[Auth System<br/>(login.php, logout.php)]
        Pages[Public/Private Pages<br/>(index.php, rooms.php, bookings.php)]
        Core[Core Logic<br/>(includes/functions.php)]
        Config[Configuration<br/>(config.php)]
    end
    
    subgraph "Database Server (MySQL)"
        DB[(hotel_db)]
    end

    User -->|Interacts| Browser
    Browser -->|HTTP GET/POST| Auth
    Browser -->|HTTP GET/POST| Pages
    
    Auth -.->|Includes| Core
    Pages -.->|Includes| Core
    Core -.->|Includes| Config
    
    Core -->|PDO Queries| DB
    DB -->|Result Set| Core
```

### Component Details

| Component | Implementation | Description |
| :--- | :--- | :--- |
| **Frontend** | HTML/CSS | `assets/style.css` provides styling. Pages are rendered server-side. |
| **Backend** | PHP 7/8 | `includes/functions.php` contains core functions like `get_total_rooms()`, `check_login()`. |
| **Database** | MySQL | Hosted on `localhost`. Tables: `users`, `rooms`, `guests`, `bookings`. |
| **Auth** | PHP Session | Session-based auth using `$_SESSION['user_id']`. |

