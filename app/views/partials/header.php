<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'My App'; ?></title>
    <!-- Add CSS Links here -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="/">My App</a>
                <div class="collapse navbar-collapse">
                  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">Dashboard</a>
                    </li>
                  </ul>
                    
                    <?php if ($currentUser ?? null): ?>
                         <span class="navbar-text me-4">
                                Welcome, <?php echo htmlspecialchars($currentUser->name ?? ''); ?>
                            </span>
                    <?php endif; ?>
                   
                   <ul class="navbar-nav">
                     <?php if ($currentUser ?? null): ?>
                        <li class="nav-item">
                           <a class="nav-link" href="/logout">Logout</a>
                         </li>
                     <?php else: ?>
                         <li class="nav-item">
                            <a class="nav-link" href="/login">Login</a>
                         </li>
                    <?php endif; ?>
                  </ul>
                </div>
            </div>
        </nav>
    </header>