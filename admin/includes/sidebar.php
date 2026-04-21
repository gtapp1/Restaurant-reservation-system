<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['admin_role'] ?? 2;

function sidebarLink(string $href, string $icon, string $label, string $current): void {
    $active = ($current === $href) ? ' active' : '';
    echo "<li><a href=\"{$href}\" class=\"{$active}\"><i class=\"bi bi-{$icon}\"></i> {$label}</a></li>\n";
}
?>
<div id="adminSidebar">
  <div class="sidebar-brand">
    <i class="bi bi-fire"></i> La Flamme
  </div>
  <ul class="sidebar-nav">
    <li class="sidebar-section-label">Overview</li>
    <?php sidebarLink('dashboard.php', 'speedometer2', 'Dashboard', $currentPage); ?>
    <li class="sidebar-divider"></li>
    <li class="sidebar-section-label">Management</li>
    <?php sidebarLink('reservations.php', 'calendar-check', 'Reservations', $currentPage); ?>
    <?php sidebarLink('users.php', 'people', 'Users', $currentPage); ?>
    <?php if ($role == 1): ?>
    <?php sidebarLink('menu.php', 'egg-fried', 'Menu Items', $currentPage); ?>
    <?php endif; ?>
    <li class="sidebar-divider"></li>
    <li class="sidebar-section-label">Analytics</li>
    <?php sidebarLink('reports.php', 'bar-chart-line', 'Reports', $currentPage); ?>
    <?php if ($role == 1): ?>
    <li class="sidebar-divider"></li>
    <li class="sidebar-section-label">System</li>
    <?php sidebarLink('admins.php', 'shield-lock', 'Admin Accounts', $currentPage); ?>
    <?php endif; ?>
  </ul>
  <div class="sidebar-footer">
    Signed in as <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></strong><br>
    <?= ($_SESSION['admin_role'] ?? 2) == 1 ? '<span style="color:#d4af37;font-size:.7rem;">Super Admin</span>' : '<span style="color:#888;font-size:.7rem;">Staff</span>' ?><br>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
</div>
