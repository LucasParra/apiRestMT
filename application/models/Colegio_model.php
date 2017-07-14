<?php
class Colegio_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }


    public function get_all()
    {
        $this->db->from('colegio');
        $this->db->order_by('nombre');
        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->result() : false;
    }

    public function get_cursos($id_colegio)
    {
        $sql = "SELECT c.id_curso, n.nivel, l.letra, n.id_nivel, c.curso
                FROM curso c
                JOIN letra l on c.letra_id_letra = l.id_letra
                JOIN nivel n on c.nivel_id_nivel = n.id_nivel
                WHERE colegio_id_colegio = $id_colegio
                ORDER BY c.nivel_id_nivel, c.letra_id_letra";

        $query = $this->db->query($sql);
        if($query->num_rows() > 0)
            return $query->result();
        else
            return false;
    }

    public function get_niveles($id_colegio)
    {
        $sql = "SELECT DISTINCT nivel_id_nivel, n.nivel FROM curso c
                JOIN nivel n ON c.nivel_id_nivel = n.id_nivel
                WHERE colegio_id_colegio = $id_colegio
                ORDER BY nivel_id_nivel ASC";
        $query = $this->db->query($sql);

        if($query->num_rows() > 0)
            return $query->result();
        else

            return false;
    }

     public function insert_curso($colegio_id, $id_letra, $id_nivel)
    {
        $nivel = $id_nivel."°";
        $letra = array('A','B','C','D','E','F','G', 'H','I','J','K','L');
        if($id_nivel > 8)
        {
            $nivel_tmp = $id_nivel - 8;
            $nivel = $nivel_tmp."°ME";
        }

        $data = array(
            'curso' => $nivel." ".$letra[$id_letra-1],
            'letra_id_letra' => $id_letra,
            'nivel_id_nivel' => $id_nivel,
            'colegio_id_colegio' => $colegio_id
            );

        if($this->db->insert('curso',$data))
            {
                $data['id_curso']=$this->db->insert_id();
                return true;
            }
        else
            return false;
    }

public function c_has_u($id_usuario)
{
        $retorno=false;

         $this->db->select('c.id_curso, c.curso, c.colegio_id_colegio,co.nombre');
         $this->db->from('curso_has_usuario cu');
         $this->db->join('curso c', 'c.id_curso=cu.curso_id_curso');
         $this->db->join('colegio co', 'co.id_colegio=c.colegio_id_colegio');
         $this->db->join('usuario u', 'u.id_usuario=cu.usuario_id_usuario');
        $this->db->where('u.id_usuario', $id_usuario);
        $query = $this->db->get();


        if($query->num_rows() > 0)
            return $query->result();
        else
            return false;
}

    public function insert_colegio(&$data)
    {
        pg_query("BEGIN");

        if($this->db->insert('colegio',$data))
        {
            $idNewColegio=$this->db->insert_id();
            $directorio = '/var/www/html/aeduc2016/informes/'.$idNewColegio;

            if (!file_exists($directorio))
            {
                umask(0);
                if(!mkdir($directorio,0777))
                {
                    pg_query("ROLLBACK");
                    return false;
                }
            }

            $schemaExist = $this->db->query("SELECT * FROM information_schema.schemata WHERE schema_name = '".$idNewColegio."'");
            if($schemaExist->num_rows() < 1)
            {
                $query = $this->db->query("select create_schema('".$idNewColegio."');");
                $result = $query->row();

                if($result->create_schema == 'Done')
                {
                    pg_query("COMMIT");
                    return true;
                }
                else
                {
                    pg_query("ROLLBACK");
                    return false;
                }
            }
            else
            {
                pg_query("COMMIT");
                return true;
            }
        }
        else
        {
            pg_query("ROLLBACK");
            return false;
        }
    }

    public function get_colegios()
    {
        $this->db->select(' c.id_colegio, c.nombre , telefono , email ,  cm.nombre comuna');
        $this->db->from('colegio c');
        $this->db->join('comuna cm', 'c.comuna_id_comuna=cm.id_comuna');

        $query = $this->db->get();

        if($query->num_rows() > 0)
            return $query->result();
        else
            return false;
    }

        public function get_us_colegio($id_colegio)
    {
        $this->db->select('u.id_usuario, u.nombre, u.apellido, u.username');
        $this->db->from('usuario u');
        $this->db->join('usuario_has_colegio uc', 'uc.usuario_id_usuario=u.id_usuario');
        $this->db->join('colegio c', 'uc.colegio_id_colegio=c.id_colegio');
        $this->db->where('c.id_colegio', $id_colegio);

         $query = $this->db->get();

        if($query->num_rows() > 0)
            return $query->result();
        else
            return false;
    }

    public function get_colegio($id_colegio)
    {
        $this->db->select('c.nombre , c.rbd , c.director, c.direccion, c.telefono, c.email ,  cm.nombre comuna');
        $this->db->from('colegio c');
        $this->db->join('comuna cm', 'c.comuna_id_comuna=cm.id_comuna');
        $this->db->where('c.id_colegio', $id_colegio);

        $query = $this->db->get();

        if($query->num_rows() > 0)
            return $query->result();
        else
            return false;
    }


    function exists($id_colegio)
    {
        $this->db->from('colegio');
        $this->db->where('id_colegio',$id_colegio);
        $query = $this->db->get();

        return ($query->num_rows()==1);
    }


    public function save(&$data, $id_colegio=false)
    {
        if (!$id_colegio or !$this->exists($id_colegio))
        {
            if($this->db->insert('colegio',$data))
            {
                $data['id_colegio']=$this->db->insert_id();
                return true;
            }
            return false;
        }

        $this->db->where('id_colegio', $id_colegio);

        return $this->db->update('colegio',$data);
    }


     function exists_u_has_c($id_colegio)
    {
        $this->db->from('usuario_has_colegio');
        $this->db->where('colegio_id_colegio',$id_colegio);
        $query = $this->db->get();

        return ($query->num_rows()==1);
    }

    public function usuario_has_colegio($id_profesor, $id_colegio)
    {
         $data = array(
            'usuario_id_usuario' => $id_profesor,
            'colegio_id_colegio' => $id_colegio,
            );

        if (!$this->exists_u_has_c($id_colegio))
        {

            if($this->db->insert('usuario_has_colegio',$data))
            {
                $data['id_colegio']=$this->db->insert_id();
                return true;
            }
            return false;
        }

        $this->db->where('colegio_id_colegio', $id_colegio);

        return $this->db->update('usuario_has_colegio',$data);
    }

}
?>
