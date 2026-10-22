    <?php

    class Calendarios extends Controller
    {
        // =========================================================
        // MODEL DE CONSULTAS
        // Responsável por acessar os dados das consultas no banco.
        // =========================================================

        private $consulta;


        // =========================================================
        // CONSTRUTOR
        // Carrega o Model "Consulta".
        //
        // IMPORTANTE:
        // O arquivo App/Models/Consulta.php precisa existir para
        // este Controller funcionar.
        // =========================================================

        public function __construct()
        {
            $this->consulta = $this->model('Consulta');
        }


        // =========================================================
        // PÁGINA PRINCIPAL DO CALENDÁRIO
        //
        // Busca as consultas cadastradas no banco e envia essas
        // informações para a View do calendário.
        // =========================================================

        public function calendario()
        {
            // Busca todas as consultas cadastradas.
            $consultas = $this->consulta->listarConsultas();


            // Dados enviados para a View.
            $dados = [
                'titulo' => 'Página de calendário',
                'descricao' => 'pagina calendário',
                'consultas' => $consultas
            ];


            // Abre a página do calendário.
            $this->view(
                'calendarios/calendario',
                $dados
            );
        }


        // =========================================================
        // VISUALIZAR CONSULTA
        //
        // Abre a página responsável por visualizar os detalhes
        // de uma consulta.
        // =========================================================

        public function visualizarConsulta()
        {
            $dados = [
                'titulo' => 'Visualizar Consulta',
                'descricao' =>
                    'Página para visualizar detalhes da consulta'
            ];


            // Abre a View de visualização.
            $this->view(
                'calendarios/visualizarConsulta',
                $dados
            );
        }


        // =========================================================
        // CADASTRAR CONSULTA
        //
        // Recebe os dados enviados pelo formulário e salva uma
        // nova consulta no banco de dados.
        // =========================================================

        public function cadastrarConsulta()
        {
            // Recebe os dados enviados pelo formulário.
            $formulario = filter_input_array(
                INPUT_POST,
                FILTER_SANITIZE_SPECIAL_CHARS
            );


            // Verifica se o formulário realmente foi enviado.
            if (isset($formulario)) {

                // =================================================
                // ORGANIZA OS DADOS DA CONSULTA
                //
                // Os nomes abaixo precisam corresponder aos campos
                // utilizados pelo formulário.
                // =================================================

                $dados = [

                    // Data da consulta.
                    'cons_data' =>
                        $formulario['dataConsulta'],

                    // Horário da consulta.
                    'cons_horario' =>
                        $formulario['horaConsulta'],

                    // Tipo da consulta.
                    'cons_tipo' =>
                        $formulario['tipoConsulta'],

                    // Descrição da consulta.
                    'cons_descricao' =>
                        $formulario['descricaoConsulta'],

                    // Observações adicionais.
                    'cons_observacao' =>
                        $formulario['observacaoConsulta']
                ];


                // =================================================
                // SALVA A CONSULTA
                // =================================================

                $this->consulta->cadastrar($dados);


                // =================================================
                // MENSAGEM DE SUCESSO
                // =================================================

                $_SESSION['mensagem'] =
                    'Consulta cadastrada com sucesso!';

                $_SESSION['tipo_msg'] =
                    'success';


                // =================================================
                // VOLTA PARA O CALENDÁRIO
                // =================================================

                header(
                    'Location: ' .
                    URL .
                    '/calendarios/calendario'
                );

                exit;
            }
        }

      // =========================================================
    // LISTAR CONSULTAS PARA O CALENDÁRIO
    // Retorna os eventos em formato JSON, com cores por tipo.
    // =========================================================

    public function listarConsultas()
    {
        // Limpa o buffer para evitar HTML antes do JSON.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Busca as consultas cadastradas no banco.
        $consultas = $this->consulta->listarConsultas();

        // Prepara os eventos enviados ao JavaScript.
        $eventos = [];

        foreach ($consultas as $consulta) {
            $eventos[] = [
                'id' => (int) $consulta->cons_id,
                'data' => $consulta->cons_data,
                'horario' => $consulta->cons_horario,
                'tipo' => $consulta->cons_tipo,
                'descricao' => $consulta->cons_descricao ?? '',
                'observacao' => $consulta->cons_observacao ?? '',

                // Cada tipo recebe sua própria cor.
                'cor' => $this->obterCorPorTipo(
                    $consulta->cons_tipo
                ),

                // Identifica se a consulta já passou.
                'status' => (
                    $consulta->cons_data < date('Y-m-d')
                ) ? 'concluido' : 'proximo'
            ];
        }

        // Informa ao navegador que a resposta é JSON.
        header('Content-Type: application/json; charset=utf-8');

        // Envia os eventos para o calendário.
        echo json_encode(
            $eventos,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }


    // =========================================================
    // COR DOS COMPROMISSOS
    // Escolhe uma cor conforme o tipo cadastrado.
    // =========================================================

    private function obterCorPorTipo($tipo)
    {
        $cores = [
            'Consulta - Periódica' => '#198754',
            'Consulta - Emergencial' => '#dc3545',
            'Avaliação' => '#0d6efd',
            'Psicóloga' => '#ffc107',
            'Retorno' => '#6f42c1'
        ];

        // Tipos não cadastrados na lista recebem cinza.
        return $cores[trim($tipo)] ?? '#6c757d';
    }
    }