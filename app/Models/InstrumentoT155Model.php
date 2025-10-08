<?php

namespace app\Models;

use app\Core\BaseModel;
use app\Core\DataTableT155;
use PDO;

class InstrumentoT155Model extends BaseModel
{
    //private $db;
    private $tabla = 'instrumento_t155'; // ✅ Tabla asociada a este modelo


    // Listar todos los registros
    public function obtenerTodos()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tabla}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un registro por ID
    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tabla} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear nuevo instrumento
    public function crear($tag, $plataforma)
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->tabla} (tag, plataforma) VALUES (?, ?)");
        return $stmt->execute([$tag, $plataforma]);
    }

    // Actualizar un instrumento por ID
    public function actualizar($id, $tag, $plataforma)
    {
        $stmt = $this->db->prepare("UPDATE {$this->tabla} SET tag = ?, plataforma = ? WHERE id = ?");
        return $stmt->execute([$tag, $plataforma, $id]);
    }

    // Eliminar por ID
    public function eliminar($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabla} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Usado en DataTable, pero se puede mejorar si hay JOIN
    public function datatable()
    {

        /*
            con variable tabla y columnas se armara lo siguiente:

            SELECT i.id, i.tag, p.nombre FROM instrumento_t155 i J
            OIN plataformas p ON i.plataforma = p.id
            
        */
      
        $tabla = 'instrumento_t155 i JOIN plataformas p ON i.plataforma = p.id';
        $columnas = ['i.id', 'i.tag', 'i.plataforma','p.ubicacion '];

        $dt = new DataTableT155($this->db,$tabla, $columnas);
        return $dt->procesar();
    }

    // OPCIONAL: Verificar si existe un nombre duplicado (para validar en backend)
    public function existeNombre($tag, $idExcluir = null)
    {
        if ($idExcluir) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->tabla} WHERE nombre = ? AND id != ?");
            $stmt->execute([$tag, $idExcluir]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->tabla} WHERE nombre = ?");
            $stmt->execute([$tag]);
        }
        return $stmt->fetchColumn() > 0;
    }
}

?>