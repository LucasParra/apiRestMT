<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class ColegioApi extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('token_model');
        $this->load->model('colegio_model');
    }
    public function getToken_get()
    {
      $this->token_model->eliminarTokensVencidos();
    	$this->response(($this->get('username') == "AEDUC" && $this->get('password') == "MIDETEED2017") ? $this->token_model->crearToken() : FALSE);
    }
    public function verificarToken_get()
    {
      $this->token_model->eliminarTokensVencidos();
      if($this->token_model->verificarToken($this->get('token')))
      {
        $this->response(TRUE);
      }
      else
      {
        $this->response(FALSE);
      }
    }
    public function getColegios_get()
    {
      $this->token_model->eliminarTokensVencidos();
      $token = $this->token_model->verificarToken($this->get('token'));
      if($token)
      {
        $this->response($this->colegio_model->get_all());
      }
    }
    public function insertColegio_post()
    {
      $this->token_model->eliminarTokensVencidos();
      $token = $this->token_model->verificarToken($this->post('token'));
      $aux = FALSE;
      if($token)
      {
        $data = array(
            'nombre' => $this->post('nombre'),
            'rbd' => $this->post('rbd'),
            'director' => $this->post('director'),
            'direccion' => $this->post('direccion'),
            'telefono' => $this->post('telefono'),
            'email' => $this->post('email'),
            'comuna_id_comuna' => $this->post("comuna_id_comuna"),
            );
        $aux = $this->insertarColegio($data);
      }
      $this->response($aux);
    }
    private function insertarColegio($data)
    {
      return($this->colegio_model->insert_colegio($data)) ? TRUE : FALSE;
    }
}
