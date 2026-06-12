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
* JWT Authentication (geplant)

### Frontend

* Vue 3
* Vite
* Pinia (State Management)
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

## ⚙️ Installation

### 1. Repository clonen

```bash
git clone https://github.com/DEIN_USERNAME/task-manager.git
cd task-manager
```

---

## 🧩 Backend Setup (Symfony)

```bash
cd backend

composer install
```

### .env konfigurieren

```env
DATABASE_URL="postgresql://postgres:password@127.0.0.1:5432/taskmanager?serverVersion=17&charset=utf8"
```

### Datenbank erstellen

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Server starten

```bash
symfony server:start
```

Backend läuft dann auf:

```
http://127.0.0.1:8000
```

---

## 🎨 Frontend Setup (Vue)

```bash
cd frontend

npm install
npm run dev
```

Frontend läuft auf:

```
http://localhost:5173
```

---

## 📦 Features (geplant)

### MVP

* User Registrierung & Login
* Boards erstellen
* Listen (To Do / In Progress / Done)
* Tasks erstellen & bearbeiten
* Tasks verschieben

### Erweiterungen

* Drag & Drop (Kanban)
* Labels & Tags
* Deadlines
* Team Collaboration
* Activity Log

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

## 🧠 Lernziel

Dieses Projekt dient dazu, folgende Konzepte zu lernen:

* REST API Entwicklung mit Symfony
* Vue 3 Composition API
* Authentication (JWT)
* Datenbank Design mit Doctrine
* State Management mit Pinia
* Fullstack Architektur

---

## 📌 Status

🚧 Projekt befindet sich in aktiver Entwicklung

---

## 👨‍💻 Autor

Erstellt im Rahmen eines Lernprojekts für Fullstack Development.
