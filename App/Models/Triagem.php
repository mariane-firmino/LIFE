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

    // Respostas das triagens do aluno, agrupadas por triagem (mesma data/hora
    // e mesmo servidor) e separadas em parte psicológica e física.
    // Devolve da mais nova para a mais antiga:
    //   [ ['data' => ..., 'serv_nome' => ..., 'func_nome' => ...,
    //      'psicologica' => [obj pergunta/resposta...], 'fisica' => [...]], ... ]
    public function listarPorAluno($alunoId)
    {
        $this->db->query("
            SELECT
                tr.tria_id,
                tr.tria_data,
                tr.serv_id,
                tr.tria_pergunta AS pergunta,
                tr.tria_resposta AS resposta,
                s.serv_nome,
                f.func_nome
            FROM triagem tr
            LEFT JOIN servidor s ON tr.serv_id = s.serv_id
            LEFT JOIN funcao f ON s.func_id = f.func_id
            WHERE tr.alun_id = :id
              AND tr.tria_pergunta IS NOT NULL
            ORDER BY tr.tria_data DESC, tr.tria_id ASC
        ");
        $this->db->bind(':id', (int) $alunoId);

        $triagens = [];
        foreach ($this->db->resultados() as $linha) {
            $chave = $linha->tria_data . '|' . $linha->serv_id;

            if (!isset($triagens[$chave])) {
                $triagens[$chave] = [
                    'data' => $linha->tria_data,
                    'serv_nome' => $linha->serv_nome,
                    'func_nome' => $linha->func_nome,
                    'psicologica' => [],
                    'fisica' => [],
                ];
            }

            $triagens[$chave][TriagemPerguntas::area($linha->pergunta)][] = $linha;
        }

        return array_values($triagens);
    }
}
