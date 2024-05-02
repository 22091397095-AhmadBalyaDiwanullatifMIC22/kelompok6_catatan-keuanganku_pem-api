<?php
// session_start();
// ob_start();

$koneksi = mysqli_connect('localhost', 'root', '', 'catatan_keuangan');

function acak($panjang)
{
    $karakter = '1234567890';
    $string = '';
    for ($i = 0; $i < $panjang; $i++) {
        $pos = rand(0, strlen($karakter) - 1);
        $string .= $karakter[$pos];
    }
    return $string;
}

function register($dataRegister)
{
    global $koneksi;

    $email = mysqli_real_escape_string($koneksi, htmlspecialchars(stripslashes($dataRegister['email-registrasi'])));
    $username = mysqli_real_escape_string($koneksi, htmlspecialchars(stripslashes($dataRegister['username-registrasi'])));
    $password = mysqli_real_escape_string($koneksi, htmlspecialchars($dataRegister['password-registrasi']));
    $passwordConfirm = mysqli_real_escape_string($koneksi, htmlspecialchars($dataRegister['password-confirm']));

    $cekUser = mysqli_query($koneksi, "SELECT email, username FROM users WHERE email = '$email' OR username = '$username'");

    if (mysqli_num_rows($cekUser) > 0) {
        echo "<script>alert('Username / email telah dipakai!');</script>";
        return false;
    }

    if ($password != $passwordConfirm) {
        echo "<script>alert('Password konfirmasi harus sama');</script>";
        return false;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    $no_rek = acak(12);
    $sukses = mysqli_query($koneksi, "INSERT INTO users (email, username, password, no_rek) VALUES ('$email', '$username', '$password', '$no_rek')");

    if ($sukses) {
        echo "<script>alert('Akun anda berhasil didaftarkan!');</script>";
    } else {
        echo "<script>alert('Akun anda gagal didaftarkan');</script>";
        return false;
    }

    return mysqli_affected_rows($koneksi);
}

function login($dataLogin)
{
    global $koneksi;

    $email = mysqli_real_escape_string($koneksi, $dataLogin['user-email']);
    $username = mysqli_real_escape_string($koneksi, $dataLogin['user-email']);
    $password = mysqli_real_escape_string($koneksi, $dataLogin['password-login']);

    $cekUser = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email' OR username = '$username'");

    if (mysqli_num_rows($cekUser) === 1) {
        $hasil = mysqli_fetch_assoc($cekUser);

        if (password_verify($password, $hasil["password"])) {
            if ($hasil['status'] == 'aktif') {
                $_SESSION['user'] = $hasil['username'];
                $_SESSION['level'] = $hasil['level'];
                $_SESSION['login'] = true;

                if ($hasil['level'] == 'admin') {
                    header('Location: administrator');
                } elseif ($hasil['level'] == 'user') {
                    header('Location: dashboard');
                }

                if (isset($_POST['rememberme'])) {
                    setcookie('login', $hasil['username'], time() + 3600);
                    setcookie('level', $hasil['level'], time() + 3600);
                    setcookie('id', $hasil['id_user'], time() + 3600);
                    setcookie('key', hash('sha256', $hasil['username']), time() + 3600);
                }
            } else {
                echo "<script>alert('Akun anda dinonaktifkan oleh admin!');</script>";
                return false;
            }
        } else {
            echo "<script>alert('Username / password salah!');</script>";
            return false;
        }
    } else {
        echo "<script>alert('Username / password salah!');</script>";
        return false;
    }

    return mysqli_affected_rows($koneksi);
}
?>
