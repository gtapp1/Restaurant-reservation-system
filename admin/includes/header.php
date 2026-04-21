<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>La Flamme Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ===== Admin Global Styles ===== */
*, body { font-family: 'Poppins', sans-serif; }
body { background: #0a0a0a; color: #e0e0e0; min-height: 100vh; display: flex; margin: 0; padding: 0; }

/* Sidebar */
#adminSidebar {
  width: 240px; min-height: 100vh; background: #111;
  border-right: 1px solid #1f1f1f; padding: 0; flex-shrink: 0;
  display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh;
  overflow-y: auto;
}
.sidebar-brand {
  padding: 1.25rem 1rem; border-bottom: 1px solid #222;
  color: #d4af37; font-weight: 700; font-size: 1rem;
  letter-spacing: .06em; text-transform: uppercase;
  display: flex; align-items: center; gap: .6rem;
}
.sidebar-brand i { font-size: 1.3rem; }
.sidebar-nav { list-style: none; padding: .75rem 0; margin: 0; flex: 1; }
.sidebar-nav li a {
  display: flex; align-items: center; gap: .65rem;
  padding: .6rem 1.25rem; color: #888; font-size: .82rem;
  text-decoration: none; transition: all .2s;
  border-left: 3px solid transparent;
}
.sidebar-nav li a:hover,
.sidebar-nav li a.active {
  color: #d4af37; background: rgba(212,175,55,.08);
  border-left-color: #d4af37;
}
.sidebar-nav li a i { font-size: 1rem; width: 20px; }
.sidebar-divider { margin: .4rem 1rem; border-top: 1px solid #1f1f1f; }
.sidebar-section-label {
  padding: .5rem 1.25rem .25rem; font-size: .65rem; color: #444;
  text-transform: uppercase; letter-spacing: .1em;
}
.sidebar-footer {
  padding: .9rem 1.2rem; border-top: 1px solid #1f1f1f;
  font-size: .75rem; color: #555; line-height: 1.5;
}
.sidebar-footer strong { color: #888; }
.sidebar-footer a { color: #dc3545; text-decoration: none; font-size: .75rem; }
.sidebar-footer a:hover { text-decoration: underline; }

/* Main */
#adminMain { flex: 1; display: flex; flex-direction: column; overflow-x: hidden; min-width: 0; }
.admin-topbar {
  background: #0e0e0e; border-bottom: 1px solid #1f1f1f;
  padding: .85rem 1.5rem; display: flex; align-items: center;
  justify-content: space-between; position: sticky; top: 0; z-index: 100;
}
.admin-topbar .page-title { font-size: 1rem; font-weight: 600; color: #d4af37; margin: 0; }
.admin-topbar .admin-user { font-size: .78rem; color: #666; }
.admin-content { padding: 1.75rem; flex: 1; }

/* Stat Cards */
.stat-card {
  background: #111; border: 1px solid #1e1e1e; border-radius: 8px;
  padding: 1.25rem 1.4rem;
  transition: border-color .25s, box-shadow .25s, transform .25s;
}
.stat-card:hover {
  border-color: #d4af37; box-shadow: 0 0 14px rgba(212,175,55,.18);
  transform: translateY(-2px);
}
.stat-card .stat-number { font-size: 1.9rem; font-weight: 700; color: #d4af37; line-height: 1.1; }
.stat-card .stat-label  { font-size: .7rem; color: #666; text-transform: uppercase; letter-spacing: .08em; margin-top: .35rem; }
.stat-card .stat-icon   { font-size: 2rem; color: rgba(212,175,55,.2); }

/* Admin Table */
.admin-table { margin: 0; width: 100%; --bs-table-bg: transparent; --bs-table-color: #c0c0c0; }
.admin-table thead th {
  background-color: #0a0a0a !important; color: #d4af37 !important; font-size: .72rem;
  text-transform: uppercase; letter-spacing: .08em;
  border-bottom: 1px solid #222 !important; border-top: none; padding: .75rem 1rem;
  white-space: nowrap;
}
.admin-table tbody tr { border-bottom: 1px solid #161616; background-color: transparent !important; }
.admin-table tbody tr:last-child { border-bottom: none; }
.admin-table tbody tr:hover td { background-color: #141414 !important; }
.admin-table tbody td {
  padding: .6rem 1rem; font-size: .82rem; color: #c0c0c0 !important;
  vertical-align: middle; border: none; background-color: transparent !important;
}
.admin-table tfoot td {
  padding: .65rem 1rem; font-size: .82rem; border-top: 1px solid #222 !important;
  border-bottom: none; background-color: transparent !important; color: #c0c0c0 !important;
}

/* Status badges */
.badge-status {
  padding: .3em .7em; border-radius: 20px; font-size: .7rem;
  font-weight: 600; letter-spacing: .04em;
}
.badge-pending   { background: rgba(255,193,7,.12);  color: #ffc107; }
.badge-confirmed { background: rgba(32,201,151,.12); color: #20c997; }
.badge-cancelled { background: rgba(220,53,69,.12);  color: #e05b68; }
.badge-completed { background: rgba(13,110,253,.12); color: #6ea8fe; }

/* Admin card */
.admin-card {
  background: #111; border: 1px solid #1e1e1e; border-radius: 8px;
  padding: 1.4rem;
}

/* Forms */
.form-control, .form-select {
  background: #181818; border: 1px solid #2a2a2a; color: #e0e0e0;
  border-radius: 5px;
}
.form-control:focus, .form-select:focus {
  background: #181818; color: #e0e0e0;
  border-color: #d4af37; box-shadow: 0 0 0 .2rem rgba(212,175,55,.15);
}
.form-control::placeholder { color: #444; }
.form-label { font-size: .76rem; color: #d4af37; font-weight: 600; letter-spacing: .04em; margin-bottom: .35rem; }

/* Buttons */
.btn-gold { background: #d4af37; color: #000; font-weight: 700; }
.btn-gold:hover { background: #c19d2f; color: #000; }
.btn-outline-warning { border-color: #d4af37; color: #d4af37; }
.btn-outline-warning:hover { background: #d4af37; color: #000; }

/* Pagination */
.pagination .page-link { background: #1a1a1a; border-color: #2a2a2a; color: #aaa; }
.pagination .page-link:hover { background: #222; color: #d4af37; border-color: #d4af37; }
.pagination .page-item.active .page-link { background: #d4af37; border-color: #d4af37; color: #000; }

/* Alerts */
.alert-success { background: rgba(32,201,151,.1); border-color: rgba(32,201,151,.3); color: #20c997; }
.alert-danger  { background: rgba(220,53,69,.1);   border-color: rgba(220,53,69,.3);  color: #e05b68; }

/* Responsive */
@media (max-width: 768px) {
  #adminSidebar { display: none; }
  .admin-content { padding: 1rem; }
}
</style>
</head>
<body>
