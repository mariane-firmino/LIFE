<?php

class Triagem
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Salva uma triagem: uma linha por pergunta respondida, todas com a mesma data/hora
    public function salvar($alunoId, $servidorId, array $respostas)
    {
        try {
            $this->db->beginTransaction();

            $data = date('Y-m-d H:i:s');

            foreach ($respostas as $r) {
                $this->db->query("
                    INSERT INTO triagem (serv_id, alun_id, tria_pergunta, tria_resposta, tria_data)
                    VALUES (:serv_id, :alun_id, :pergunta, :resposta, :data)
                ");
                $this->db->bind(':serv_id', (int) $servidorId);
                $this->db->bind(':alun_id', (int) $alunoId);
                $this->db->bind(':pergunta', $r['pergunta']);
                $this->db->bind(':resposta', $r['resposta']);
                $this->db->bind(':data', $data);

                if (!$this->db->executa()) {
                    throw new Exception('Falha ao gravar a resposta da triagem');
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
