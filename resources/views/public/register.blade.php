<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>
<body>
        
    <div class="decor shadow"></div>
        <h1 class="title">Registrasi</h1>
        <div class="login-box">
            <div class="login-container">
                <form action="process/register" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label>Nama:</label><br>
                    <input type="text" required name="name" class="input-box"><br>
                    <label>No. Telepon: +62 </label><br>
                    <input type="text" min="1" required name="telephone" class="input-box"><br>
                    <label>Password:</label><br>
                    <input type="password" required name="password" class="input-box"><br>
                    <label>Konfirmasi Password:</label><br>
                    <input type="password" required name="password_confirmation" class="input-box"><br>
                    <label>Sekolah</label><br>
                    <label>Gambar Potret:</label><br>
                    <input type="file" name="image"><br>
                    
                    <a href="/login" class="input-btn btn btn-secondary">Kembali</a>
                    <input type="submit" name="register" value="Daftar" class="input-btn btn btn-primary">
                </form>
            </div>
        </div>
    @if ($errors->any())
    <div class="alert alert-danger error-box">
        <h3>Error</h3>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li><br>
            @endforeach
        </ul>
    </div>
    @endif
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</body>
</html>
<style>
    body{
        background: linear-gradient(turquoise, teal);
        background-attachment: fixed;
        margin:0 auto;
        overflow: hidden;
    }


    .login-box{
        background-color: white;
        position: absolute;
        bottom: 20%;
        left: 50%;
        transform: translate(-50%, 20%);
        width: 70%;
        height: 65%;
        border: 0.75vh solid lightgrey;
        border-radius: 5vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .btn-login{
        background: linear-gradient(to right, turquoise, teal);
        color: white;
        width: 150px;
        font-weight: bold;
    }

    .btn-login:hover{
        background: linear-gradient(to right, teal, black);
        color: white;
    }

    .input-box{
        margin: 5px;
        border-radius: 30px;
        padding-left: 5px;
        background-color: #EFEFEF;
    }

    .input-btn{
        margin: 5px;
        border-radius: 20px;
    }

    .error-box{
        position: absolute;
        bottom: 50%;
        left: 5%;
        transform: translate(-5%, 50%);
        width: 30%;
        /* border-radius: 5%; */
        padding: 20px;
    
    }

    .decor{
        border-radius: 50%;
        background-color: white;
        position: absolute;
        top: -27%;
        left: 50%;
        transform: translate(-50%, 27%);
        width: 140%;
        height: 40%;
    }

    .title{
        position: absolute;
        top: 5vh;
        left: 5vh;
        border-radius: 5vh;
        text-align: left;
        text-shadow: 0px 3px rgba(0, 0, 0, .15);
    }
</style>
