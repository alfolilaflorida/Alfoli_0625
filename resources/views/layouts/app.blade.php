<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Alfolí')</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #3f51b5;
            --secondary-color: #f4f7fc;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #1e2a38;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary-color);
            color: var(--dark-color);
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #5c6bc0) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #5c6bc0);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(63, 81, 181, 0.3);
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), #5c6bc0);
            color: white;
            border: none;
            font-weight: 600;
        }

        .table tbody tr {
            transition: background-color 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(63, 81, 181, 0.05);
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
        }

        .footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
        }

        @media (max-width: 768px) {
            .navbar-nav {
                text-align: center;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    @auth
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Alfolí" class="me-2">
                <span class="fw-bold">Sistema Alfolí</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @if(auth()->user()->canView())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-chart-line me-1"></i> Dashboard
                            </a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->canManage())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('alfoli.index') }}">
                                <i class="fas fa-table me-1"></i> Ingreso Alfolí
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('productos.vencimiento.index') }}">
                                <i class="fas fa-recycle me-1"></i> Productos Caducados
                            </a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->canView())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('notificaciones.index') }}">
                                <i class="fas fa-bell me-1"></i> Notificaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('alertas.index') }}">
                                <i class="fas fa-business-time me-1"></i> Alertas
                            </a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('usuarios.index') }}">
                                <i class="fas fa-users-gear me-1"></i> Usuarios
                            </a>
                        </li>
                    @endif
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> {{ auth()->user()->nombre_completo }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text">{{ auth()->user()->rol_display }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endauth

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} Sistema Alfolí — Desarrollado por Aura Solutions Group</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- CSRF Token Setup -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')
</body>
</html>