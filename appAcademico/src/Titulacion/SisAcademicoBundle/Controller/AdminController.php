<?php

namespace Titulacion\SisAcademicoBundle\Controller;
//git
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Titulacion\SisAcademicoBundle\Helper\UgServices;

class AdminController extends Controller
{


    public function calendario_carreraAction(Request $request){

        $UgServices   = new UgServices;
        $rsEventos = $UgServices->getConsultaSoloEventos(1); #como parametros enviaremos siempre 1


        return $this->render('TitulacionSisAcademicoBundle:Admin:calendario_carrera.html.twig', array('data' => $rsEventos));
    }

    public function cambio_passwordAction(){
        return $this->render('TitulacionSisAcademicoBundle:Admin:cambio_password.html.twig', array());
    }

    public function ingreso_nuevo_passAction(Request $request){
        #obtenemos los datos enviados por get

            $session=$request->getSession();
            $Email= $session->get('mail');
            $Nombre = $session->get('nom_usuario');
            $username    = $request->request->get('user');
            $username    = $request->request->get('pass1');
            $password    = $request->request->get('pass2');
           
              
        
        #llamamos a la consulta del webservice
        $UgServices = new UgServices;
        
              
            $username     = $request->request->get('user');
            $password     = $request->request->get('pass');
            $password1    = $request->request->get('pass1');

            $UgServices   = new UgServices;
            $salt         = "µ≈α|⊥ε¢ʟ@δσ";
            $passwordEncr = password_hash($password, PASSWORD_BCRYPT, array("cost" => 14, "salt" => $salt));
            $passwordNuevoEncr = password_hash($password1, PASSWORD_BCRYPT, array("cost" => 14, "salt" => $salt));

            $dataMant = $UgServices->mantenimientoUsuario($username,$passwordEncr,'','',$passwordNuevoEncr,'A');

                if ( is_object($dataMant)) {
                    $estado = $dataMant ->PI_ESTADO;
                     $message = $dataMant ->PV_MENSAJE;
                }

                    //echo "<pre>";
                    //var_dump($estado.'-----'.$message);
                    //echo "</pre>";
                    //exit();
                if ($estado == "1") {
                  $mailer    = $this->container->get('mailer');
                    $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com',465,'ssl')
                                ->setUsername('titulacion.php@gmail.com')
                                ->setPassword('sc123456');
                   //$mailer  = \Swift_Mailer($transport);
                    $message = \Swift_Message::newInstance('test')
                                ->setSubject("Contraseña Cambiada Correctamente")
                                ->setFrom('titulacion.php@gmail.com','Universidad de Guayaquil')
                                ->setTo($Email)
                                ->setBody("$Nombre usted ha Cambiado la Contraseña Exitosamente, Su nueva contraseña es $password1");
                    // ->setBody($this->renderView('TitulacionSisAcademicoBundle:Admin:Comtraseña.html.twig'),'text/html', 'utf8');
                    $this->get('mailer')->send($message);   
                }

            $respuesta = array(
               "Codigo" => $estado ,
               "Mensaje" => $message,
            );

          return new Response(json_encode($respuesta));
    }

    public function cargar_eventosAction(Request $request)
    {
        #llamamos a la consulta del webservice
        $UgServices = new UgServices;
        // $data = $UgServices->getEventos($start,$end);
        //         $data = array(
        //   "id"=>
        //   "1",
        //   "title"=>
        //    "hola",
        //   "content"=>
        //    "mundo",
        //   "start_date"=>
        //   "2015-09-28 22:00:00",
        //   "end_date"=>
        //    "2015-09-29 22:00:00",
        //   "access_url_id"=>
        //   "1",
        //   "all_day"=>
        //    "0"
        // );
        echo '<pre>'; var_dump("hi"); exit();
    }

    public function cargar_eventos_carrera_userAction(Request $request)
    {

        #llamamos a la consulta del webservice
        $UgServices = new UgServices;

        return $this->render('TitulacionSisAcademicoBundle:Admin:calendario_academico_carrera_user.html.twig', array());
    }
    /**
     * [Action que permite crear un vento del calendario academico]
     * @param  Request $request [description]
     */
    public function crear_eventos_academicosAction(Request $request){
        #llamamos a la consulta del webservice
        $UgServices = new UgServices;
        $evento    = $request->request->get('evento');
        $rsInsertEvent = $UgServices->crearEventos($evento);

        return new Response($rsInsertEvent);
        // echo '<pre>'; var_dump($rsInsertEvent); exit();
    }#end function

    public function insertar_eventos_calendarioAction(Request $request){

        $id_ciclo = 19;
        $UgServices = new UgServices;
        $id_evento    = $request->request->get('id_evento');
        $fec_desde    = $request->request->get('start');
        $fec_hasta    = $request->request->get('end');
        // $date = date_format($fec_desde, 'Y-m-d H:i:s');
        // echo '<pre>'; var_dump($date); exit();
        $session=$request->getSession();
        $id_usuario = $session->get("id_user");
        $id_usuario = 11;
        $rsInsertEvent = $UgServices->insertarEventosCalendario($id_evento,$id_ciclo,$fec_desde,$fec_hasta,$id_usuario);
        // echo '<pre>'; var_dump($rsInsertEvent); exit();
        return new Response($rsInsertEvent);
    }#end function


//---------------------------------------------------------------------------------------------------------------------------------------//
//---------------------------------------------------------------------------------------------------------------------------------------//
//---------------------------------------------------------------------------------------------------------------------------------------//


    public function consulta_estudiantes_carreraAction(Request $request){

           $session=$request->getSession();
            $perfilEst   = $this->container->getParameter('perfilEst');
            $perfilDoc   = $this->container->getParameter('perfilDoc');
            $perfilAdmin = $this->container->getParameter('perfilAdmin'); 
            $perfilEstDoc = $this->container->getParameter('perfilEstDoc'); 
            $perfilEstAdm = $this->container->getParameter('perfilEstAdm'); 
            $perfilDocAdm = $this->container->getParameter('perfilDocAdm');

            $datos = array();
            if ($session->has("perfil")) 
            {
                if ($session->get('perfil') == $perfilDoc || $session->get('perfil') == $perfilEstAdm || $session->get('perfil') == $perfilDocAdm) 
                {
                    try
                    {
                    
                        $lcCarrera="";
                        $idAdministrador=$session->get("id_user");
                        $idRol=$perfilAdmin;
                        $ciclo = "";
                        $nombreCarrera = "";
                        $idCarrera = "";
                        $tienePermiso = "0";
                        $lsEstudiantes = array();
                        $lsOpciones = array();
                        $Carreras = array();
                        $UgServices = new UgServices;
                        $xml = $UgServices->getConsultaCarreras($idAdministrador,$idRol);

                        if ( is_object($xml))
                            {
                               foreach($xml->registros->registro as $lcCarreras) 
                               {
                                    $idCarrera=$lcCarreras->id_sa_carrera;
                                    $nombreCarrera = $lcCarreras->nombre;
                                    $ciclo = $lcCarreras->id_sa_ciclo_detalle;
                                    $materiaObject = array( 'nombre' => $nombreCarrera,
                                                            'idCarrera'=> $idCarrera,
                                                            'idCiclo'=> $ciclo,
                                                            );
                                    array_push($Carreras, $materiaObject); 
                               } 
                            
                            $cuantos=count($Carreras);
                            if ($cuantos!=0)
                            {
                                $tienePermiso="1";
                            }
                            if ($tienePermiso == "1")
                            {
                                $lcValor = "";
                                $lcDescripcion= "";
                                $xmlEstadosMatricula = $UgServices->getEstadosMatricula();
                                if ($xmlEstadosMatricula)
                                {
                                    foreach ($xmlEstadosMatricula as $lcOpciones) {

                                       $lcValor=$lcOpciones['codigo'];
                                       $lcDescripcion=$lcOpciones['nombre'];

                                       $Opcion=array('nombre'=> $lcDescripcion,
                                                          'codigo'=> $lcValor,
                                                       );
                                        array_push($lsOpciones, $Opcion); 
                                    }
                                }
                                $datos =  array( 'carreras' =>  $Carreras,
                               
                                   'parametroCombos' => $lsOpciones,
                                 
                                     'mostrar' =>  'SI'
                                   );
                                             

                                 return $this->render('TitulacionSisAcademicoBundle:Admin:consultaEstudiantesXCarrera.html.twig', array('estudianteCarrera' => $datos));
                               }

                           }
                              
                     }
                     catch (\Exception $e){}
               }
               else
               {
                  $this->get('session')->getFlashBag()->add(
                                'mensaje',
                                'Los datos ingresados no son válidos'
                            );
                    return $this->redirect($this->generateUrl('titulacion_sis_academico_homepage'));
               }
           }
           else
           {
                $this->get('session')->getFlashBag()->add(
                                      'mensaje',
                                      'Los datos ingresados no son válidos'
                                  );
                    return $this->redirect($this->generateUrl('titulacion_sis_academico_homepage'));
            }
       return $this->render('TitulacionSisAcademicoBundle:Admin:consultaEstudiantesXCarrera.html.twig', array('estudianteCarrera' => $datos  = array('mostrar' =>  'NO' )));
    }



  public function estudiante_carrera_selectedAction(Request $request)
  {
            
                $session=$request->getSession();
                $UgServices = new UgServices;
                $idCarrera = $request->request->get('carrera');
                $ciclo = $request->request->get('ciclo');
                $opcion = $request->request->get('opcion');
                $identificacion = $request->request->get('identificacion');
                $datos = array('estado' => 'NO');
                $lsPorcentaje = array();
                $idEstadoEstudiante = $request->request->get('estadoMatricula');
                if($opcion == "1"){

                    $session->set("id_EstadoMatriculaEstudiante_"+$idCarrera,"0");
                    $xml = $UgServices->getConsultaPorcentajeEstudianteCarrera($ciclo,$idCarrera);

                    if ($xml)
                       {
                          $colors = array('#F56954','#00A65A','#F39C12','#00C0EF','#3C8DBC','#D2D6DE','#2ecc40','#01ff70','#ffdc00','#ff851b','#ff4136','#85144b', '#f012be','#b10dc9','#111111','#aaaaaa','#dddddd');
                          $iColor = 0;
                           foreach($xml as $lcEstudiantes) 
                           {
                              $Estudiantes=
                                        array("value" => $lcEstudiantes['cantidad'],
                                               "color" =>$colors[$iColor],
                                               "highlight" =>$colors[$iColor],
                                              "label"=> $lcEstudiantes['nombre'],
                                              );
                                        $iColor = $iColor +1;
                                array_push($lsPorcentaje, $Estudiantes); 
                            } 
                        }
                }else{
                        $session->set("id_EstadoMatriculaEstudiante_"+$idCarrera,$idEstadoEstudiante);
                }
                $xmlEstudiante = $UgServices->getConsultaEstudiantes_InscritosMatriculados($ciclo,$idCarrera,$idEstadoEstudiante,$identificacion);
                $lcNombre = "";
                $lcEstadoMatricula= "";
                $lsEstudiantes = array();
               
                 if ($xmlEstudiante)
                               {
                                   foreach($xmlEstudiante as $lcEstudiantes) 
                                   { 
                                      $lcNombre=$lcEstudiantes['nombrecompleto'];
                                      $lcEstadoMatricula=$lcEstudiantes['estadoestudiante'];
                                      $Estudiantes=array("cedulaestudiante" => $lcEstudiantes['cedulaestudiante'],
                                                          "nombre"=> $lcNombre,
                                                         "estadoMateria"=> $lcEstadoMatricula,                
                                                      );
                                       array_push($lsEstudiantes, $Estudiantes); 
                                   } 
                               }
             if($opcion == "1"){
                           $datos =  array( "listadoEstudiante" =>  $lsEstudiantes,
                                            "listadoPorcentaje" =>  $lsPorcentaje,
                    'estado' => 'SI'
                                    );
                }else{
                   $datos =  array( "listadoEstudiante" =>  $lsEstudiantes,
                    'estado' => 'SI'
                                    );
                }
        return new Response(json_encode($datos));
  }


    public function pdfEstudiantes_InscritosMatriculadosAction(Request $request, $idCarrera, $ciclo,$carrera )
    {     
             $session      = $request->getSession();
              $idEstadoEstudiante =  $session->get('id_EstadoMatriculaEstudiante'+$idCarrera);

              $UgServices = new UgServices;
      
                $xmlEstudiante = $UgServices->getConsultaEstudiantes_InscritosMatriculados($ciclo,$idCarrera,$idEstadoEstudiante,"");
                              
                  

                                if ($xmlEstudiante)
                                {
                                 $pdf= " <html> 
                                            <body>
                                            <img width='5%' src='images/menu/ug_logo.png'/>
                                            <table align='center'>
                                            <tr>
                                              <td align='center'>
                                                <b>Registro de Estudiantes</b>
                                              </td>
                                            <tr>
                                            <tr>
                                            <td>
                                              <b>".$carrera."</b>
                                            </td>
                                            </tr>
                                            </table>
                                            <div class='col-lg-12'>
                                            <br><br><br><br>
                                            <table class='table table-striped table-bordered' border='1' width='100%' >
                                                     <thead>
                                                        <tr>
                                                                <th colspan='3'   style='text-align: center !important;background-color: #337AB7 !important;color: white!important;'>REPORTE DEL CLICLO</th>
                                                        </tr>
                                                        <tr>
                                                            
                                                            <th style='text-align: center !important;'>Identificación</th> 
                                                            <th style='text-align: center !important;'>Nombre Alumno</th>
                                                            <th style='text-align: center !important;'>Estado Matricula</th>
                                                           
                                                        </tr>";

                                    foreach($xmlEstudiante as $lcEstudiantes) 
                                    {
                                       $lcNombre=$lcEstudiantes['nombrecompleto'];
                                       $lcEstadoMatricula=$lcEstudiantes['estadoestudiante'];
                                        $cedula=$lcEstudiantes['cedulaestudiante'];
                                        $pdf.="<tr><td>".$cedula."</td><td>".$lcNombre."</td><td>".$lcEstadoMatricula."</td></tr>";
                                    } 

                                     $pdf.="</table>";
                                     $pdf.="</div></body></html>";
                                }
                  $mpdfService = $this->get('tfox.mpdfport');
                  $mPDF = $mpdfService->getMpdf();
                  $mPDF->AddPage('','','1','i','on');
                  $mPDF->WriteHTML($pdf);
                  
                  return new response($mPDF->Output());
    }#end function
        
    

}