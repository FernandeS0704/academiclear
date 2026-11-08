<?php

class Comentario {

    private $texto;
    private $autor_id;
    private $documento_id;

    // Obter e setar o TEXTO
    public function getTexto() {
        return $this->texto;
    }
    public function setTexto($texto) {
        $this->texto = $texto;
    }

    // O ID DO AUTOR
    public function getAutorId() {
        return $this->autor_id;
    }
    public function setAutorId($autor_id) {
        $this->autor_id = $autor_id;
    }

    // ID DO DOCUMENTO
    public function getDocumentoId() {
        return $this->documento_id;
    }
    public function setDocumentoId($documento_id) {
        $this->documento_id = $documento_id;
    }
}