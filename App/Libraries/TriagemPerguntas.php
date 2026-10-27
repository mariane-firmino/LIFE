<?php

// Perguntas, tipos e opções válidas das triagens (devem ser iguais aos formulários em App/Views/triagens)
class TriagemPerguntas
{
    // Nas duas triagens, as perguntas 1 a 6 são da parte psicológica
    // ("Situação Psicoemocional"); a partir da 7 é a parte física
    // ("Saúde Sexual e Reprodutiva"). Igual à View adicionar_triagem.php.
    public const PRIMEIRA_PERGUNTA_FISICA = 7;

    // As duas páginas de triagem: chave da URL => título.
    public const PARTES = [
        'psicologica' => 'Triagem Psicológica',
        'fisica' => 'Triagem Física',
    ];

    public static function por($tipo)
    {
        return $tipo === 'masculina' ? self::masculina() : self::feminina();
    }

    // Só as perguntas de uma parte ('psicologica' ou 'fisica'), mantendo
    // a numeração original (os campos do formulário são "pergunta{N}").
    public static function parte($tipo, $parte)
    {
        $fisica = $parte === 'fisica';

        return array_filter(
            self::por($tipo),
            function ($numero) use ($fisica) {
                return ($numero >= self::PRIMEIRA_PERGUNTA_FISICA) === $fisica;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    // Diz se uma pergunta já salva é da parte 'psicologica' ou 'fisica'.
    // Compara pelo texto, que é o que fica gravado na tabela triagem.
    public static function area($textoPergunta)
    {
        static $psicologicas = null;

        if ($psicologicas === null) {
            $psicologicas = [];
            foreach ([self::masculina(), self::feminina()] as $lista) {
                foreach ($lista as $numero => $p) {
                    if ($numero < self::PRIMEIRA_PERGUNTA_FISICA) {
                        $psicologicas[$p['pergunta']] = true;
                    }
                }
            }
        }

        return isset($psicologicas[$textoPergunta]) ? 'psicologica' : 'fisica';
    }

    public static function masculina()
    {
        return [
            1 => [
                'pergunta' => 'Quando você olha no espelho, você fica:',
                'tipo' => 'select',
                'opcoes' => ['Satisfeito', 'Insatisfeito', 'Indiferente'],
                'obrigatoria' => true,
            ],
            2 => [
                'pergunta' => 'Diariamente você se sente:',
                'tipo' => 'select',
                'opcoes' => ['Motivado', 'Desmotivado', 'Irritado', 'Calmo'],
                'obrigatoria' => true,
            ],
            3 => [
                'pergunta' => 'O que você mais gosta em si mesmo?',
                'tipo' => 'texto',
                'opcoes' => [],
                'obrigatoria' => true,
            ],
            4 => [
                'pergunta' => 'Qual é o seu hobby? (O que você mais gosta de fazer?)',
                'tipo' => 'texto',
                'opcoes' => [],
                'obrigatoria' => true,
            ],
            5 => [
                'pergunta' => 'Você possui algum objetivo de vida?',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não', 'Nenhum / Nunca pensei nisso antes'],
                'obrigatoria' => true,
            ],
            6 => [
                'pergunta' => 'Geralmente, como são seus pensamentos?',
                'tipo' => 'select',
                'opcoes' => ['Bons / Motivadores / Esperançosos', 'Ruins / Desanimadores / Desesperançosos', 'Não sei'],
                'obrigatoria' => true,
            ],
            7 => [
                'pergunta' => 'Você possui vida sexual ativa? ou (Já teve a primeira relação sexual?)',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não'],
                'obrigatoria' => true,
            ],
            8 => [
                'pergunta' => 'Quando você tem ralações sexuais, faz uso de preservativo?',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não'],
                'obrigatoria' => true,
            ],
            9 => [
                'pergunta' => 'Por quê? (caso a responsta anterior seja NÃO)',
                'tipo' => 'select',
                'opcoes' => ['Não QUER usar', 'Não SABE usar', 'Sente vergonha'],
                'obrigatoria' => false,
            ],
            10 => [
                'pergunta' => 'Você sabe o que é IST? (Infecção sexualmente transmissível)',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não', 'Nunca ouvi falar', 'Já ouvi falar, mas não sei o que é'],
                'obrigatoria' => true,
            ],
        ];
    }

    public static function feminina()
    {
        return [
            1 => [
                'pergunta' => 'Quando você olha no espelho, você fica:',
                'tipo' => 'select',
                'opcoes' => ['Satisfeito', 'Insatisfeito', 'Indiferente'],
                'obrigatoria' => true,
            ],
            2 => [
                'pergunta' => 'Diariamente você se sente:',
                'tipo' => 'select',
                'opcoes' => ['Motivado', 'Desmotivado', 'Irritado', 'Calmo'],
                'obrigatoria' => true,
            ],
            3 => [
                'pergunta' => 'O que você mais gosta em si mesmo?',
                'tipo' => 'texto',
                'opcoes' => [],
                'obrigatoria' => true,
            ],
            4 => [
                'pergunta' => 'Qual é o seu hobby? (O que você mais gosta de fazer?)',
                'tipo' => 'texto',
                'opcoes' => [],
                'obrigatoria' => true,
            ],
            5 => [
                'pergunta' => 'Você possui algum objetivo de vida?',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não', 'Nenhum / Nunca pensei nisso antes'],
                'obrigatoria' => true,
            ],
            6 => [
                'pergunta' => 'Geralmente, como são seus pensamentos?',
                'tipo' => 'select',
                'opcoes' => ['Bons / Motivadores / Esperançosos', 'Ruins / Desanimadores / Desesperançosos', 'Não sei'],
                'obrigatoria' => true,
            ],
            7 => [
                'pergunta' => 'Você já teve a sua primeira menstruação?',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não'],
                'obrigatoria' => true,
            ],
            8 => [
                'pergunta' => 'Durante seu período menstrual, você sente cólicas?',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não'],
                'obrigatoria' => true,
            ],
            9 => [
                'pergunta' => 'Qual a intensidade das cólicas?',
                'tipo' => 'select',
                'opcoes' => ['0 - Nenhuma', '1 - Leve', '2 -', '3 -', '4 -', '5 - Moderada', '6 -', '7 -', '8 -', '9 -', '10 - Muito Intensa'],
                'obrigatoria' => false,
            ],
            10 => [
                'pergunta' => 'Costuma tomar alguma medicação para cólicas?',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não'],
                'obrigatoria' => true,
            ],
            11 => [
                'pergunta' => 'Qual medicação você toma, quando está com cólicas?',
                'tipo' => 'texto',
                'opcoes' => [],
                'obrigatoria' => false,
            ],
            12 => [
                'pergunta' => 'Você possui vida sexual ativa? ou (Já teve a primeira relação sexual?)',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não'],
                'obrigatoria' => false,
            ],
            13 => [
                'pergunta' => 'Usa algum método contraceptivo? (caso a resposta anterior seja SIM)',
                'tipo' => 'select',
                'opcoes' => ['Nenhum', 'Preservativo', 'Pílula', 'Injetável', 'DIU', 'Outro'],
                'obrigatoria' => false,
            ],
            14 => [
                'pergunta' => 'Por quê? (caso a responsta anterior seja NÃO)',
                'tipo' => 'select',
                'opcoes' => ['Não QUER usar', 'Não SABE usar', 'Sente vergonha'],
                'obrigatoria' => false,
            ],
            15 => [
                'pergunta' => 'Você já foi em alguma consulta Ginecológica?',
                'tipo' => 'select',
                'opcoes' => ['Sim', 'Não'],
                'obrigatoria' => true,
            ],
        ];
    }
}
