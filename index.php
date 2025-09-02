<?php
//ini_set('display_errors', 1) ;
session_start();
$_SESSION['logged-in'] = false;

require_once './src/Database.php';
$db = Database::getInstance();

$err = '';

if(isset($_POST['submit'])){
 
  $email = $_POST['email'];
  $password = $_POST['password'];

  if(strlen($email) < 1 ){
    $err = 'Please enter email address';
  } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $err = 'Please enter a valid email adddress';
  } else if(strlen($password) < 1){
    $err = "Please enter your password";
  } else {
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = '$email'";
    $res = $db->query($sql);

    if($res->num_rows < 1){
      $err = "No user found";
    } else {
      $user = $res->fetch_object();

      if(password_verify($password , $user->password)){
          $_SESSION['logged-in'] = true;
          $_SESSION['user'] = $user;
          header('Location: ./dashboard.php');
          exit();
      } else {
        $err = "Wrong username or password";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Helpdesk - Login</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  
  <style>
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 0.8; }
      50% { opacity: 1; }
    }
    
    :root {
      --treasury-navy: #1e3a5f;
      --treasury-gold: #c9a96e;
      --treasury-green: #2d5a3d;
      --treasury-blue: #4a90a4;
      --treasury-amber: #b8860b;
      --treasury-burgundy: #722f37;
      --treasury-dark: #2c3e50;
      --treasury-light: #f8f9fc;
      --treasury-brown: #8B4513;
      --treasury-tan: #D2B48C;
      --kenya-red: #922529;
      --kenya-green: #008C51;
    }
    
    body {
      background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-dark) 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      position: relative;
      overflow-x: hidden;
    }
    
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="a" cx=".5" cy=".5" r=".5"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="20" cy="20" r="10" fill="url(%23a)"/><circle cx="80" cy="80" r="15" fill="url(%23a)"/><circle cx="40" cy="70" r="8" fill="url(%23a)"/><circle cx="90" cy="30" r="12" fill="url(%23a)"/></svg>') repeat;
      animation: float 6s ease-in-out infinite;
      pointer-events: none;
    }
    
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.1),
        0 2px 8px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 420px;
      overflow: hidden;
      animation: fadeInUp 0.8s ease-out;
      position: relative;
    }
    
    .login-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--kenya-red), var(--treasury-dark), var(--kenya-green));
      background-size: 200% 100%;
      animation: shimmer 2s linear infinite;
    }
    
    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }
    
    .login-header {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
      padding: 2rem;
      text-align: center;
      position: relative;
    }
    
    .logo {
      width: 240px;
      height: 160px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 0.25rem;
      overflow: hidden;
      animation: pulse 3s ease-in-out infinite;
      transition: transform 0.3s ease;
    }
    
    .logo:hover {
      transform: scale(1.05);
    }
    
    .logo i {
      font-size: 1.8rem;
      color: #495057;
    }
    
    .login-title {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #212529;
      margin-top: -1.25rem;
      background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-burgundy));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInUp 1s ease-out 0.3s both;
    }
    
    .login-subtitle {
      font-size: 0.95rem;
      color: #6c757d;
      margin-bottom: 0;
      animation: fadeInUp 1s ease-out 0.5s both;
      opacity: 0.8;
    }
    
    .login-body {
      padding: 2rem;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .form-control {
      border: 2px solid rgba(233, 236, 239, 0.5);
      border-radius: 12px;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      width: 100%;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(5px);
      position: relative;
    }
    
    .form-control:focus {
      border-color: var(--treasury-brown);
      box-shadow: 
        0 0 0 0.2rem rgba(139, 69, 19, 0.15),
        0 4px 12px rgba(139, 69, 19, 0.1);
      outline: none;
      background: rgba(255, 255, 255, 0.95);
      transform: translateY(-1px);
    }
    
    .form-control:hover {
      border-color: var(--treasury-tan);
      transform: translateY(-1px);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-burgundy) 100%);
      border: 2px solid transparent;
      border-radius: 12px;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      font-weight: 600;
      color: white;
      width: 100%;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .btn-primary::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .btn-primary:hover::before {
      left: 100%;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 
        0 8px 25px rgba(139, 69, 19, 0.4),
        0 4px 12px rgba(114, 47, 55, 0.3);
      background: linear-gradient(135deg, var(--treasury-burgundy) 0%, var(--treasury-dark) 100%);
    }
    
    .btn-primary:active {
      transform: translateY(0);
      box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
    }
    
    .form-check {
      margin-bottom: 1.5rem;
    }
    
    .form-check-input {
      border: 2px solid #e9ecef;
    }
    
    .form-check-input:checked {
      background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-burgundy));
      border-color: #667eea;
    }
    
    .form-check-input:focus {
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    
    .form-check-label {
      color: #495057;
      font-weight: 500;
    }
    
    .alert {
      border: 1px solid #f8d7da;
      border-radius: 8px;
      padding: 1rem;
      margin-top: 1rem;
      background: #f8d7da;
      color: #721c24;
      font-weight: 500;
    }
    
    .alert-success {
      background: #d4edda;
      border-color: #c3e6cb;
      color: #155724;
    }
    
    .forgot-password {
      text-align: center;
      margin-top: 1rem;
    }
    
    .forgot-password a {
      color: #495057;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      border-bottom: 1px solid transparent;
      transition: border-color 0.2s;
    }
    
    .forgot-password a:hover {
      border-bottom-color: #495057;
    }
    
    .back-to-login {
      text-align: center;
      margin-top: 1rem;
    }
    
    .back-to-login a {
      color: #495057;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      border-bottom: 1px solid transparent;
      transition: border-color 0.2s;
    }
    
    .back-to-login a:hover {
      border-bottom-color: #495057;
    }
    
    .reset-header {
      text-align: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #e9ecef;
    }
    
    .reset-header h5 {
      color: #212529;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .reset-header p {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 0;
    }
    
    /* Mobile responsive */
    @media (max-width: 576px) {
      .login-card {
        margin: 10px;
        border-radius: 10px;
      }
      
      .login-header {
        padding: 1.5rem;
      }
      
      .login-body {
        padding: 1.5rem;
      }
      
      .login-title {
        font-size: 1.5rem;
      }
      
      .logo {
        width: 160px;
        height: 110px;
      }
      
      .logo i {
        font-size: 1.5rem;
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="logo">
          <img src="images/tnt.logo" alt="TNT Logo" style="width: auto; height: 100%; object-fit: contain; max-width: 100%;">
        </div>
        <h1 class="login-title">ICT Helpdesk</h1>
        <p class="login-subtitle">Support System</p>
      </div>
      
      <div class="login-body">
        <?php if(!isset($_GET['forgot'])): ?>
          <!-- Login Form -->
          <form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" style="animation: fadeInUp 1s ease-out 0.7s both; opacity: 0;">
            <div class="form-group" style="animation: fadeInUp 0.6s ease-out 0.8s both; opacity: 0;">
              <label class="form-label" for="email">
                <i class="fas fa-envelope me-2"></i>Email Address
              </label>
              <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" autofocus value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group" style="animation: fadeInUp 0.6s ease-out 1s both; opacity: 0;">
              <label class="form-label" for="password">
                <i class="fas fa-lock me-2"></i>Password
              </label>
              <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
            </div>
            
            <div class="form-check" style="animation: fadeInUp 0.6s ease-out 1.2s both; opacity: 0;">
              <input class="form-check-input" type="checkbox" value="remember-me" id="rememberMe">
              <label class="form-check-label" for="rememberMe">
                Remember me
              </label>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary" style="animation: fadeInUp 0.6s ease-out 1.4s both; opacity: 0;">
              <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
          </form>

          <?php if(strlen($err) > 1) :?>
            <div class="alert" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Error:</strong> <?php echo htmlspecialchars($err);?>
            </div>
          <?php endif?>
          
          <div class="forgot-password">
            <a href="?forgot=1">
              <i class="fas fa-key me-1"></i>Forgot your password?
            </a>
          </div>
          
        <?php else: ?>
          <!-- Forgot Password Form -->
          <form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>?forgot=1">
            <div class="reset-header">
              <h5>Reset Password</h5>
              <p>Enter your email address and we'll help you reset your password.</p>
            </div>
            
            <div class="form-group">
              <label class="form-label" for="reset_email">
                <i class="fas fa-envelope me-2"></i>Email Address
              </label>
              <input type="email" name="reset_email" class="form-control" id="reset_email" placeholder="Enter your email" autofocus required>
            </div>
            
            <button type="submit" name="reset_password" class="btn btn-primary">
              <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
            </button>
          </form>

          <?php if(isset($_POST['reset_password'])): ?>
            <?php
            $reset_email = $_POST['reset_email'];
            if(filter_var($reset_email, FILTER_VALIDATE_EMAIL)) {
              // Check if email exists in database
              $check_sql = "SELECT id FROM users WHERE email = '$reset_email'";
              $check_res = $db->query($check_sql);
              
              if($check_res->num_rows > 0) {
                // Here you would normally send an email with reset instructions
                // For demo purposes, we'll just show a success message
                echo '<div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> Password reset instructions have been sent to your email.
                      </div>';
              } else {
                echo '<div class="alert" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> No account found with that email address.
                      </div>';
              }
            } else {
              echo '<div class="alert" role="alert">
                      <i class="fas fa-exclamation-triangle me-2"></i>
                      <strong>Error:</strong> Please enter a valid email address.
                    </div>';
            }
            ?>
          <?php endif?>
          
          <div class="back-to-login">
            <a href="<?php echo $_SERVER['PHP_SELF']?>">
              <i class="fas fa-arrow-left me-1"></i>Back to Login
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>