<?php
class Token_model extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function verificarToken($token)
    {
        $aux = FALSE;
        $query = $this->db->get_where('token', array('token' => $token));
        if ($query->num_rows() > 0)
        {
            $token = $query->result();
            if((strtotime($token[0]->hora_final) - time()) < 0)
            {
              $this->deleteToken($token);
            }
            else
            {
              $aux = TRUE;
            }
        }
        return $aux;
    }

    public function crearToken()
    {
        $aux = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $token = substr(str_shuffle($aux), 0, 25);
        $data =
        [
            'hora_inicio' => date('Y-m-d H:i:s'),
            'hora_final' => date("Y-m-d H:i:s", strtotime('+5 minute')),
            'token' => $token
        ];
        return ($this->db->insert('token' , $data)) ? $token : FALSE;
    }
    public function eliminarTokensVencidos()
    {
      $this->db->where('current_timestamp >','t.hora_final', FALSE);
      $this->db->delete('token t');
    }
    public function deleteToken($token)
    {
      $this->db->where('id_token', $token[0]->id_token);
      $this->db->delete('token');
    }
}
?>
