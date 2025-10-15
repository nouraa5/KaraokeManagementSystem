# KaraokeManagementSystem
Karaoke Management System

A lightweight web app for managing karaoke events.
Attendees browse songs and submit requests; admins approve, queue, and track performances in real time.

Frontend: HTML, CSS/SCSS (Bootstrap), JavaScript (ES6), DataTables, Modals, AJAX

Backend (pluggable): Any REST API (examples below show a mock API with json-server)

Deployment: Static hosting for UI (Netlify/GitHub Pages) + simple API host (Render/Heroku)

✨ Features
Audience (User)

Browse/search/filter the song catalog (artist, title, language, duration)

Request a song with name/table and optional dedication

See request status: Pending → Approved → Singing → Done

Mobile-friendly UI (Bootstrap), fast table operations (DataTables)

Admin

Real-time request queue with approve/deny, re-order (drag & drop optional), mark singing/done

Catalog management: add/edit/remove songs

Request details panel: requester info, notes, timestamps

Bulk actions (clear queue, export CSV), audit trail (optional)

Quality of Life

Smooth modals for forms & confirmations

Client-side validation

Persisted UI state (filters/sorts)

Toasts/alerts for feedback

🛠 Tech Stack

Core: HTML5, CSS/SCSS, Bootstrap 5, JavaScript (fetch/AJAX)

Tables/UX: DataTables (search, sort, pagination)

Icons (optional): Font Awesome / Bootstrap Icons

Mock API (dev): json-server

Tooling (optional): npm scripts, live-server for hot reload
