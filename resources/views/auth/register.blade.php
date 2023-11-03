<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Register | CRMS - Credit Referencing Management System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/logo-login.png" >

        <!-- App css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
        <link href="assets/css/main.css" rel="stylesheet" type="text/css"/>




    </head>

    <body class="loading authentication-bg" data-layout-config='{"darkMode":false}'>

        <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xxl-4 col-lg-5">
                        <div class="card">
                            <!-- Logo-->
                            <div class="card-header pt-4 pb-4 text-center bg-primary">
                                <a href="index.html">
                                    <span><img src="assets/images/logoname.png" alt="" height="30"></span>
                                </a>
                            </div>

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <h4 class="text-dark-50 text-center mt-0 fw-bold">Free Sign Up</h4>
                                    <p class="text-muted mb-4">Don't have an account? Create your account, it takes less than a minute </p>
                                </div>

                                @if ($errors)
                                <div >
                                    <x-auth-session-status class="alert alert-danger mb-4" :status="session('status')" />
                                    <x-auth-validation-errors class="alert alert-danger mb-4" role="alert" :errors="$errors" />
                                </div>
                                @endif
                                
                                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-Data">
                                    @csrf
                                    

                                    <div class="mb-3">
                                        <label for="emailaddress" class="form-label">Type</label>
                                        <select class="form-select" id="type" name='type' required>
                                            @foreach ($types as $key => $val)
                                                <option value="{{ $key }}">
                                                    {{$val}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="" class="mb-3">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input class="form-control" type="text" placeholder="Enter your full name" name="name"  autofocus>
                                    </div>

                                    <div id="bankname" class="mb-3 not-visible">
                                        <label for="bankname" class="form-label">Bankname</label>
                                        <input type="text" name="bankname" class="form-control" />
           
                                    </div>
                                    
                                    <div id="" class="mb-3">
                                        <label for="image" class="form-label">Choose a pic </label>
                                        <input  type="file" name="image" id="image" accept="image/jpg,image/jpeg" class="form-control" >
                                    </div>

                                    


                                    <div id="container_employee" class="mb-3">
                                        <label for="employee_number" class="form-label">Employee number</label>
                                        <input class="form-control" name="employee_number" type="number" id="employee_number"  placeholder="Enter employee number">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="emailaddress" class="form-label">Email address</label>
                                        <input class="form-control" name="email" type="email" id="emailaddress" required placeholder="Enter your email">
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="new-password" required>
                                            <div class="input-group-text" data-password="false">
                                                <span class="password-eye"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Confirm Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" id="password" name="password_confirmation" class="form-control" placeholder="Enter your password" autocomplete="new-password" required>
                                            <div class="input-group-text" data-password="false">
                                                <span class="password-eye"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkbox-signup">
                                            <label class="form-check-label" for="checkbox-signup">I accept <a href="#" class="text-muted">Terms and Conditions</a></label>
                                        </div>
                                    </div>

                                    <div class="mb-3 text-center">
                                        <button class="btn btn-primary" type="submit"> Sign Up </button>
                                    </div>

                                </form>
                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->

                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p class="text-muted">Already have account? <a href="{{ route('login') }}" class="text-muted ms-1"><b>Log In</b></a></p>
                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

        <footer class="footer footer-alt">
            2018 - <script>document.write(new Date().getFullYear())</script> © Hyper - Coderthemes.com
        </footer>

        <!-- bundle -->
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        <script src="assets/js/main.js"> </script>

        
    </body>
</html>
