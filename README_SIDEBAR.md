# Reusable Sidebar Component

## Overview
The `sidebar.php` file contains a modern, reusable sidebar component that can be included across all pages in the helpdesk system.

## Features
- **Clean Modern Design**: White gradient background with subtle shadows
- **Organized Navigation**: Sections for Overview, Tickets, Personal, and Management
- **Active Page Highlighting**: Automatically highlights the current page
- **Responsive Design**: Collapses to icon-only on mobile devices
- **User Profile Section**: Shows current user with sign-out functionality

## Usage

### 1. Include the Sidebar
Simply include the sidebar component in your page:

```php
<div class="app-shell">
  <?php include 'sidebar.php'; ?>
  
  <!-- Your page content here -->
  <section class="content">
    <!-- Page content -->
  </section>
</div>
```

### 2. Required CSS Structure
Add this basic CSS to your page:

```css
<style>
  :root { --bg-soft: #f8fafc; }
  html, body { height: 100%; }
  body { background: var(--bg-soft); }
  .app-shell { display: flex; height: 100vh; }
  .content { 
    padding: 1rem 1.25rem 2rem;
    height: 100vh;
    overflow-y: auto;
    flex: 1;
  }
</style>
```

### 3. Active Page Detection
The sidebar automatically detects the current page using `basename($_SERVER['PHP_SELF'])` and highlights the appropriate navigation item.

## File Structure
```
/helpdesk-core-php/
├── sidebar.php          # Reusable sidebar component
├── users.php           # Example page using the sidebar
├── team.php            # Page to be updated
├── dashboard.php       # Page to be updated
└── other-pages.php     # Other pages to be updated
```

## Page Navigation Links
The sidebar includes links to:
- **Dashboard** (dashboard.php)
- **Open Tickets** (open.php)
- **Pending** (pending.php) 
- **Solved** (solved.php)
- **Closed** (closed.php)
- **Unassigned** (unassigned.php)
- **My Tickets** (mytickets.php)
- **Teams** (team.php)
- **Users** (users.php)

## Mobile Responsiveness
- On screens smaller than 992px, the sidebar collapses to 70px width
- Navigation labels hide, showing only icons
- User profile section adapts for mobile layout

## Customization
To customize the sidebar:
1. Edit `sidebar.php` for HTML structure changes
2. Modify the `<style>` section in `sidebar.php` for design changes
3. Update navigation links as needed

## Implementation Example
See `users.php` for a complete example of how to implement the sidebar in your pages.