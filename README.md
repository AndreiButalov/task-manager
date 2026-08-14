# Task Manager (Vue + Symfony)

Ein modernes Fullstack-Lernprojekt mit Vue.js im Frontend und Symfony im Backend.
Die Anwendung ist ein Kanban-Task-Manager ähnlich wie Trello.

---

## 🚀 Tech Stack

### Backend

* Symfony 7+
* PHP 8.4
* Doctrine ORM
* PostgreSQL
* JWT Authentication

### Frontend

* Vue 3
* Vue Router
* Axios

---

## 📁 Projektstruktur

```
task-manager/
│
├── backend/      # Symfony API
├── frontend/     # Vue App
└── README.md
```

---

## ⚙️ Installation mit Docker

### 1. Repository clonen

```bash
git clone https://github.com/AndreiButalov/task-manager.git
cd task-manager
```

### 2. Docker Container starten

```bash
docker-compose up -d
```

Dies startet automatisch:
* **Backend** (Symfony) - läuft auf `http://localhost:8000`
* **Frontend** (Vue) - läuft auf `http://localhost:5173`
* **PostgreSQL Datenbank**

### 3. Datenbank initialisieren (einmalig)

```bash
docker-compose exec backend php bin/console doctrine:migrations:migrate
```

---

## 🔐 API Struktur (Backend)

### Auth

* POST /api/register
* POST /api/login

### Boards

* GET /api/boards
* POST /api/boards
* DELETE /api/boards/{id}

### Tasks

* POST /api/tasks
* PUT /api/tasks/{id}
* DELETE /api/tasks/{id}

---
