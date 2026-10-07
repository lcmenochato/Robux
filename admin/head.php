<head>
   <title>Painel</title>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
   <link rel="icon" href="https://i.imgur.com/o9GE0gv.jpeg" type="image/x-icon">
   <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
   <link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">
   <link rel="stylesheet" href="assets/vendor/animate-css/vivify.min.css">
   <link rel="stylesheet" href="assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
   <link rel="stylesheet" href="assets/vendor/jquery-datatable/fixedeader/dataTables.fixedcolumns.bootstrap4.min.css">
   <link rel="stylesheet" href="assets/vendor/jquery-datatable/fixedeader/dataTables.fixedheader.bootstrap4.min.css">
   <link rel="stylesheet" href="assets/vendor/sweetalert/sweetalert.css"/>
   <link rel="stylesheet" href="assets/vendor/toastr/toastr.min.css">
   <link rel="stylesheet" href="assets/css/mooli.min.css">
   <script src="assets/bundles/libscripts.bundle.js"></script>    
   <script src="assets/bundles/vendorscripts.bundle.js"></script>
   <script src="assets/vendor/toastr/toastr.js"></script>
   <script src="assets/bundles/datatablescripts.bundle.js"></script>
   <script src="assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
   <script src="assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
   <script src="assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
   <script src="assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
   <script src="assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>
   <script src="assets/vendor/sweetalert/sweetalert.min.js"></script>
   <script src="assets/vendor/toastr/toastr.js"></script>
   <script src="assets/bundles/mainscripts.bundle.js" defer></script>
   <script src="js/pages/tables/jquery-datatable.js"></script>
   <style>
      /*
      CSS DO CLOAKER
      */
      .alerta-critico {
      display: block;
      padding: 14px 16px;
      margin-top: 6px;
      color: #842029;
      background-color: #f8d7da;
      border: 1px solid #f5c2c7;
      border-radius: 6px;
      font-size: 14px;
      line-height: 1.5;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      }
      /*
      CSS DO PIX GERADO
      */
      .div_contadores {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      padding: 8px;
      margin-bottom: 6px;
      }
      .contador_box {
      padding: 18px 25px;
      border-radius: 10px;
      min-width: 160px;
      text-align: center;
      transition: 0.2s ease-in-out;
      }
      .contador_box:hover {
      transform: translateY(-4px);
      }
      .contador_box small {
      display: block;
      font-size: 13px;
      color: #aaa;
      margin-bottom: 3px;
      letter-spacing: 0.5px;
      }
      .contador_box span {
      font-size: 20px;
      font-weight: bold;
      }
      @media (max-width: 768px) {
      .div_contadores {
      justify-content: space-between;
      padding: 10px;
      gap: 12px;
      }
      .contador_box {
      flex: 0 0 48%; 
      min-width: unset;
      padding: 14px;
      }
      .contador_box small {
      font-size: 10px;
      }
      .contador_box span {
      font-size: 18px;
      }
      }
      /*
      VERSÃO MOBILE 
      */
      @media (max-width: 480px) {
      .contador_box {
      flex: 0 0 100%;
      }
      .contador_box span {
      font-size: 17px;
      }
      }
      /*
      CSS DO PIXEL
      */
      .token-pixel {
      max-width: 260px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      }
      /*
      CSS PIXEL FACEBOOK E TIKTOK
      */
      .pixel_da_meta_evento h3 {
      font-size: 12px;
      margin: 0;
      padding: 0;
      font-weight: normal;
      }
      .pixel_da_meta_evento {
      text-align: center;
      }
      .pixel_do_tiktok_evento h3 {
      font-size: 12px;
      margin: 0;
      padding: 0;
      font-weight: normal;
      }
      .pixel_do_tiktok_evento {
      text-align: center;
      }
   </style>
   <style> 
      /*
      CSS DO SUCESSO
      */
      .toast-sucesso {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #16a34a;
      color: #fff;
      padding: 14px 22px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      opacity: 0;
      transform: translateY(-15px);
      pointer-events: none;
      transition: opacity 0.4s ease, transform 0.4s ease;
      z-index: 9999;
      }
      .toast-sucesso.mostrar {
      opacity: 1;
      transform: translateY(0);
      }
      /*
      CSS DO CLOAKER
      */
      .box-paises {
      border: 1px solid #999; 
      border-radius: 6px;
      padding: 10px;
      display: inline-block; 
      background: transparent;
      }
      .lista-paises {
      list-style: none;
      margin: 0;
      padding: 0;
      }
      .lista-paises li {
      margin-bottom: 4px;
      }
      .lista-paises label {
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      }
   </style>
</head>