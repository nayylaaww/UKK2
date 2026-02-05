<?php
// File: register.php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] == 'petugas') {
        header('Location: petugas/dashboard.php');
    } else {
        header('Location: peminjam/dashboard.php');
    }
    exit();
}

// Inisialisasi variabel
$errors = [];
$success = false;
$username = $nama = $email = $no_hp = $alamat = '';

// Proses form registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $role = 'peminjam'; // Default role untuk registrasi publik

    // Validasi
    if (empty($username)) {
        $errors['username'] = 'Username wajib diisi';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username minimal 3 karakter';
    } else {
        // Cek username sudah ada atau belum
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['username'] = 'Username sudah digunakan';
        }
    }

    if (empty($password)) {
        $errors['password'] = 'Password wajib diisi';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Password tidak sama';
    }

    if (empty($nama)) {
        $errors['nama'] = 'Nama lengkap wajib diisi';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    }

    // Jika tidak ada error, proses registrasi
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate kode verifikasi (untuk fitur email verification)
        $verification_code = md5(uniqid(rand(), true));
        
        // Insert ke database
        $stmt = $conn->prepare("INSERT INTO user (username, password, nama, email, no_hp, alamat, role, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $username, $hashed_password, $nama, $email, $no_hp, $alamat, $role, $verification_code);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Log activity
            logActivity($user_id, "Mendaftar akun baru");
            
            // Auto login setelah registrasi
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['nama'] = $nama;
            $_SESSION['role'] = $role;
            
            // Redirect ke dashboard peminjam
            header('Location: peminjam/dashboard.php?registered=1');
            exit();
        } else {
            $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

// Jika ada parameter admin=1, tampilkan form untuk admin menambah user
$is_admin_form = isset($_GET['admin']) && $_GET['admin'] == '1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin_form ? 'Tambah User Baru' : 'Registrasi Akun'; ?> - Aplikasi Peminjaman Alat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            display: flex;
            min-height: 600px;
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 50px;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.1);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            display: inline-block;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .logo p {
            opacity: 0.8;
            font-size: 14px;
        }

        .features {
            margin-top: 30px;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .feature i {
            margin-right: 10px;
            background: rgba(255,255,255,0.2);
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            border-radius: 50%;
            font-size: 12px;
        }

        .right-panel {
            flex: 1.5;
            padding: 40px;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            font-size: 14px;
        }

        .form-header a {
            color: #4361ee;
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 14px;
        }

        .error {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .success-message {
            background: #2ecc71;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #4361ee;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .password-strength {
            margin-top: 5px;
            height: 4px;
            border-radius: 2px;
            background: #eee;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }

        .strength-weak { background: #e74c3c; width: 25%; }
        .strength-medium { background: #f39c12; width: 50%; }
        .strength-strong { background: #2ecc71; width: 75%; }
        .strength-very-strong { background: #27ae60; width: 100%; }

        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: #777;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link i {
            margin-right: 8px;
        }

        .back-link:hover {
            color: #4361ee;
        }

        .admin-badge {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
            font-weight: normal;
        }

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
            }
            
            .left-panel {
                padding: 30px;
            }
            
            .right-panel {
                padding: 30px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .left-panel, .right-panel {
                padding: 20px;
            }
            
            .logo i {
                font-size: 40px;
                width: 70px;
                height: 70px;
                line-height: 70px;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
        }

        .terms {
            font-size: 12px;
            color: #777;
            margin-top: 15px;
            text-align: center;
        }

        .terms a {
            color: #4361ee;
            text-decoration: none;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #4361ee;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Left Panel (Info) -->
        <div class="left-panel">
            <div class="logo">
                <i class="fas fa-tools"></i>
                <h1>Peminjaman Alat</h1>
                <p>Sistem Manajemen Peminjaman</p>
            </div>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-check"></i>
                    <span>Kelola peminjaman alat dengan mudah</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check"></i>
                    <span>Pantau ketersediaan alat real-time</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check"></i>
                    <span>Notifikasi pengembalian otomatis</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check"></i>
                    <span>Laporan lengkap dan terstruktur</span>
                </div>
                <div class="feature">
                    <i class="fas fa-check"></i>
                    <span>Akses multi-level pengguna</span>
                </div>
            </div>
            
            <div style="margin-top: auto; font-size: 12px; opacity: 0.7; text-align: center;">
                <p>©️ 2026 Website Peminjaman Alat</p>
                <p>Rekayasa Perangkat Lunak</p>
            </div>
        </div>
        
        <!-- Right Panel (Form) -->
        <div class="right-panel">
            <?php if ($is_admin_form): ?>
                <a href="admin/user.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Kelola User
                </a>
            <?php else: ?>
                <a href="index.php?login=true" class="back-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Login
                </a>
            <?php endif; ?>
            
            <div class="form-header">
                <h2>
                    <?php echo $is_admin_form ? 'Tambah User Baru' : 'Buat Akun Baru'; ?>
                    <?php if ($is_admin_form): ?>
                        <span class="admin-badge">Admin Mode</span>
                    <?php endif; ?>
                </h2>
                <p>
                    <?php if ($is_admin_form): ?>
                        Tambahkan user baru untuk mengakses sistem
                    <?php else: ?>
                        Sudah punya akun? <a href="index.php?login=true">Masuk di sini</a>
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (isset($errors['general'])): ?>
                <div class="error" style="background: #ffeaea; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message show">
                    <i class="fas fa-check-circle"></i> Registrasi berhasil! Anda akan diarahkan ke halaman login.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span style="color: red;">*</span></label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username); ?>" 
                               placeholder="contoh: johndoe" 
                               required
                               oninput="checkUsernameAvailability(this.value)">
                        <div id="username-error" class="error">
                            <?php echo $errors['username'] ?? ''; ?>
                        </div>
                        <div id="username-availability" style="font-size: 12px; margin-top: 5px;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span style="color: red;">*</span></label>
                        <input type="text" id="nama" name="nama" 
                               value="<?php echo htmlspecialchars($nama); ?>" 
                               placeholder="contoh: John Doe" 
                               required>
                        <div id="nama-error" class="error">
                            <?php echo $errors['nama'] ?? ''; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" 
                               placeholder="contoh: johndoe@email.com">
                        <div id="email-error" class="error">
                            <?php echo $errors['email'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_hp">Nomor HP/WhatsApp</label>
                        <input type="tel" id="no_hp" name="no_hp" 
                               value="<?php echo htmlspecialchars($no_hp); ?>" 
                               placeholder="contoh: 081234567890">
                    </div>
                </div>
                
                <?php if ($is_admin_form): ?>
                <div class="form-group">
                    <label for="role">Role Pengguna <span style="color: red;">*</span></label>
                    <select id="role" name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="admin">Administrator</option>
                        <option value="petugas">Petugas</option>
                        <option value="peminjam" selected>Peminjam</option>
                    </select>
                </div>
                <?php else: ?>
                    <input type="hidden" name="role" value="peminjam">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group password-field">
                        <label for="password">Password <span style="color: red;">*</span></label>
                        <input type="password" id="password" name="password" 
                               placeholder="Minimal 6 karakter" 
                               required
                               oninput="checkPasswordStrength(this.value)">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="password-strength">
                            <div id="strength-bar" class="strength-bar"></div>
                        </div>
                        <div id="strength-text" class="strength-text"></div>
                        <div id="password-error" class="error">
                            <?php echo $errors['password'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <div class="form-group password-field">
                        <label for="confirm_password">Konfirmasi Password <span style="color: red;">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Ketik ulang password" 
                               required
                               oninput="checkPasswordMatch()">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div id="confirm-error" class="error">
                            <?php echo $errors['confirm_password'] ?? ''; ?>
                        </div>
                        <div id="password-match" style="font-size: 12px; margin-top: 5px;"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3" 
                              placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($alamat); ?></textarea>
                </div>
                
                <?php if (!$is_admin_form): ?>
                <div class="info-box">
                    <p><i class="fas fa-info-circle"></i> Dengan mendaftar, Anda menyetujui <a href="#">Syarat dan Ketentuan</a> yang berlaku.</p>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <?php echo $is_admin_form ? 'Tambah User' : 'Daftar Sekarang'; ?>
                    <i class="fas fa-user-plus" style="margin-left: 8px;"></i>
                </button>
                
                <?php if (!$is_admin_form): ?>
                <div class="login-link">
                    Sudah punya akun? <a href="index.php?login=true">Masuk di sini</a>
                </div>
                
                <div class="terms">
                    Dengan mendaftar, Anda menyetujui <a href="#">Terms of Service</a> dan <a href="#">Privacy Policy</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Check password strength
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            
            let strength = 0;
            let text = '';
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update strength bar
            strengthBar.className = 'strength-bar';
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
            } else if (strength <= 2) {
                strengthBar.className += ' strength-weak';
                strengthText.textContent = 'Lemah';
                strengthText.style.color = '#e74c3c';
            } else if (strength === 3) {
                strengthBar.className += ' strength-medium';
                strengthText.textContent = 'Cukup';
                strengthText.style.color = '#f39c12';
            } else if (strength === 4) {
                strengthBar.className += ' strength-strong';
                strengthText.textContent = 'Kuat';
                strengthText.style.color = '#2ecc71';
            } else {
                strengthBar.className += ' strength-very-strong';
                strengthText.textContent = 'Sangat Kuat';
                strengthText.style.color = '#27ae60';
            }
        }
        
        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchDiv.textContent = '';
                matchDiv.style.color = '';
            } else if (password === confirmPassword) {
                matchDiv.textContent = '✓ Password cocok';
                matchDiv.style.color = '#27ae60';
            } else {
                matchDiv.textContent = '✗ Password tidak cocok';
                matchDiv.style.color = '#e74c3c';
            }
        }
        
        // Check username availability
        function checkUsernameAvailability(username) {
            const availabilityDiv = document.getElementById('username-availability');
            
            if (username.length < 3) {
                availabilityDiv.textContent = '';
                return;
            }
            
            // Simulasi AJAX request
            setTimeout(() => {
                // Ini contoh saja, di production gunakan AJAX real
                const takenUsernames = ['admin', 'petugas', 'user123'];
                
                if (takenUsernames.includes(username.toLowerCase())) {
                    availabilityDiv.textContent = '✗ Username tidak tersedia';
                    availabilityDiv.style.color = '#e74c3c';
                } else {
                    availabilityDiv.textContent = '✓ Username tersedia';
                    availabilityDiv.style.color = '#27ae60';
                }
            }, 500);
        }
        
        // Form validation before submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const username = document.getElementById('username').value;
            const nama = document.getElementById('nama').value;
            
            let isValid = true;
            
            // Reset errors
            document.querySelectorAll('.error').forEach(el => el.textContent = '');
            
            // Validate username
            if (username.length < 3) {
                document.getElementById('username-error').textContent = 'Username minimal 3 karakter';
                isValid = false;
            }
            
            // Validate nama
            if (nama.length === 0) {
                document.getElementById('nama-error').textContent = 'Nama wajib diisi';
                isValid = false;
            }
            
            // Validate password
            if (password.length < 6) {
                document.getElementById('password-error').textContent = 'Password minimal 6 karakter';
                isValid = false;
            }
            
            // Validate password match
            if (password !== confirmPassword) {
                document.getElementById('confirm-error').textContent = 'Password tidak sama';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = document.querySelector('.error:not(:empty)');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Auto-focus on first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Real-time validation
        document.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
        
        function validateField(field) {
            const errorDiv = document.getElementById(field.id + '-error');
            if (!errorDiv) return;
            
            let error = '';
            
            switch(field.id) {
                case 'username':
                    if (field.value.length < 3) {
                        error = 'Username minimal 3 karakter';
                    }
                    break;
                case 'nama':
                    if (field.value.length === 0) {
                        error = 'Nama wajib diisi';
                    }
                    break;
                case 'password':
                    if (field.value.length < 6) {
                        error = 'Password minimal 6 karakter';
                    }
                    break;
                case 'email':
                    if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                        error = 'Format email tidak valid';
                    }
                    break;
            }
            
            errorDiv.textContent = error;
        }
    </script>
</body>
</html>