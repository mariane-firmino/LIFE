<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

$esc = static fn($t): string => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');

/* ---------------------------------------------------------
   EQUIPE
   categorias: front | back | full (a Full Stack aparece também
   nos filtros Front-end e Back-end).
--------------------------------------------------------- */
$equipe = [
    [
        'nome' => 'Gabriel Caminha',
        'cargo' => 'Desenvolvedor Back-end',
        'foto' => 'gabriel.jpeg',
        'categorias' => 'back',
        'texto' => 'Estou gostando muito de desenvolver esse sistema, ainda mais ao lado de meus amigos, '
            . 'está sendo uma experiência incrível.',
    ],
    [
        'nome' => 'Gustavo Henrique Braga Silva',
        'cargo' => 'Desenvolvedor Front-end',
        'foto' => 'gustavo.jpeg',
        'categorias' => 'front',
        'texto' => 'Gosto de esportes.',
    ],
    [
        'nome' => 'Laís Firmino',
        'cargo' => 'Desenvolvedora Back-end',
        'foto' => 'laís.jpeg',
        'categorias' => 'back',
        'texto' => 'Participar desse projeto no meu último ano foi muito gratificante, e com a ajuda do meu '
            . 'grupo, conseguimos um ótimo resultado.',
    ],
    [
        'nome' => 'Maria Clara',
        'cargo' => 'Desenvolvedora Front-end',
        'foto' => 'mariaC.jpeg',
        'categorias' => 'front',
        'texto' => 'Eu e meus amigos trabalhamos juntos para transformar os conhecimentos adquiridos durante '
            . 'o curso em um projeto prático. Fiquei responsável por parte do front-end, contribuindo para o '
            . 'desenvolvimento da aparência do sistema e para tornar a experiência do usuário mais simples e agradável.',
    ],
    [
        'nome' => 'Maria Eduarda',
        'cargo' => 'Desenvolvedora Front-end',
        'foto' => 'maia.jpeg',
        'categorias' => 'front',
        'texto' => 'Mesmo com algumas dificuldades, gostei muito de fazer parte disso e de ver o projeto '
            . 'ficando pronto aos poucos. Espero que ele dê certo e que realmente possa ajudar a escola.',
    ],
    [
        'nome' => 'Mariane Firmino',
        'cargo' => 'Desenvolvedora Full Stack',
        'foto' => 'mari-ft.jpeg',
        'categorias' => 'front back full',
        'texto' => 'Desenvolver esse sistema foi algo muito gratificante, principalmente por estar colaborando '
            . 'para uma melhoria no meu campus. Cada etapa foi uma experiência marcante, com muitos desafios e '
            . 'aprendizados, estou muito feliz em desenvolvê-lo junto dos meus amigos.',
    ],
    [
        'nome' => 'Rafael Caldeira',
        'cargo' => 'Desenvolvedor Back-end',
        'foto' => 'rafael-ft.jpeg',
        'categorias' => 'back',
        'texto' => 'Foi uma ótima experiência trabalhar nesse projeto. Gosto de jogos e esportes.',
    ],
];

/* ---------------------------------------------------------
   NÚMEROS
   Para mostrar o total real de alunos, o Controller pode enviar:
       $dados['stats'] = ['alunos' => 123];
--------------------------------------------------------- */
$stats = (isset($dados) && is_array($dados)) ? ($dados['stats'] ?? []) : [];

$contadores = [
    ['valor' => 20, 'sufixo' => '+', 'rotulo' => 'páginas do formulário antigo substituídas por um cadastro único'],
    ['valor' => count($equipe), 'sufixo' => '', 'rotulo' => 'desenvolvedores na equipe'],
    ['valor' => 4, 'sufixo' => '', 'rotulo' => 'recursos: cadastro, triagem, consulta e notificações'],
];

if (isset($stats['alunos'])) {
    $contadores[] = ['valor' => (int) $stats['alunos'], 'sufixo' => '', 'rotulo' => 'alunos cadastrados'];
}
?>


<div class="layout sobre-page">

    <!-- CABEÇALHO -->
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            <header class="home-topo">
                <div class="home-titulo">
                    <h1>Sobre Nós</h1>
                    <p>Informações sobre os desenvolvedores e o sistema.</p>
                </div>

                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo Life">
            </header>
        </div>
    </div>

    <main class="container p-4" id="conteudo">

        <!-- OBJETIVO -->
        <section class="sobre-bloco mb-5" aria-labelledby="titulo-objetivo">
            <h2 class="h4" id="titulo-objetivo">Objetivo do sistema</h2>
            <p>O Enfermaria foi pensado e desenvolvido com o objetivo de solucionar uma problemática que ocorre no
                ambiente escolar em relação ao bem-estar dos estudantes, oferecendo uma ferramenta específica para
                atuar nesse setor, promovendo maior eficiência e integridade. Um site com comportamentos de cadastro
                e consulta que promove o armazenamento e agilidade de informações durante a triagem, proporcionando
                um ambiente seguro onde os dados são gerenciados e acessados por meio da administração dos
                servidores da CAED.</p>
            <p>O Enfermaria apresenta uma interface eficiente e simples, para ser utilizada até por pessoas com
                pouco conhecimento tecnológico, permitindo-lhes cadastrar alunos, adicionar triagens e consultar os
                dados dos alunos. Os usuários podem utilizar diversos recursos proporcionados pelo sistema com os
                fins de zelar pela saúde dos estudantes no meio escolar. Além de contar com notificações
                personalizadas para alertar sobre consultas com alunos que precisem de atendimento periódico.</p>
        </section>

        <!-- NÚMEROS -->
        <section class="sobre-stats mb-5" aria-label="O sistema em números">
            <?php foreach ($contadores as $c): ?>
                <div class="stat">
                    <span class="stat-num" aria-hidden="true"
                        data-valor="<?= (int) $c['valor'] ?>"
                        data-sufixo="<?= $esc($c['sufixo']) ?>"><?= (int) $c['valor'] . $esc($c['sufixo']) ?></span>
                    <span class="visually-hidden"><?= (int) $c['valor'] . $esc($c['sufixo']) ?></span>
                    <span class="stat-rotulo"><?= $esc($c['rotulo']) ?></span>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- EQUIPE -->
        <section class="mb-5" aria-labelledby="titulo-equipe">
            <div class="equipe-topo">
                <h2 class="h4 mb-0" id="titulo-equipe">Quem desenvolveu</h2>

                <div class="filtros" role="group" aria-label="Filtrar equipe por área">
                    <button type="button" class="filtro ativo" data-filtro="todos" aria-pressed="true">Todos</button>
                    <button type="button" class="filtro" data-filtro="front" aria-pressed="false">Front-end</button>
                    <button type="button" class="filtro" data-filtro="back" aria-pressed="false">Back-end</button>
                    <button type="button" class="filtro" data-filtro="full" aria-pressed="false">Full Stack</button>
                </div>
            </div>

            <p class="visually-hidden" id="filtro-status" role="status" aria-live="polite"></p>

            <div class="equipe-grid" id="equipe">
                <?php foreach ($equipe as $i => $m): ?>
                    <article class="membro" data-categorias="<?= $esc($m['categorias']) ?>">
                        <div class="membro-inner">

                            <!-- FRENTE -->
                            <div class="membro-face membro-frente">
                                <img src="<?= URL ?>/img/<?= rawurlencode($m['foto']) ?>"
                                    alt="Foto de <?= $esc($m['nome']) ?>" loading="lazy">

                                <div class="membro-legenda">
                                    <h3><?= $esc($m['nome']) ?></h3>
                                    <span class="membro-cargo"><?= $esc($m['cargo']) ?></span>

                                    <button type="button" class="membro-btn" data-virar>
                                        Ler depoimento
                                        <span class="visually-hidden">de <?= $esc($m['nome']) ?></span>
                                    </button>
                                </div>
                            </div>

                            <!-- VERSO -->
                            <div class="membro-face membro-verso" inert>
                                <span class="membro-cargo"><?= $esc($m['cargo']) ?></span>
                                <h3><?= $esc($m['nome']) ?></h3>
                                <p class="membro-texto"><?= $esc($m['texto']) ?></p>

                                <button type="button" class="membro-btn" data-voltar>
                                    Voltar
                                    <span class="visually-hidden">à foto de <?= $esc($m['nome']) ?></span>
                                </button>
                            </div>

                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- JUSTIFICATIVA -->
        <section class="sobre-bloco mb-5" aria-labelledby="titulo-justificativa">
            <h2 class="h4" id="titulo-justificativa">Justificativa do sistema</h2>
            <p>A priori, esse projeto surgiu da necessidade de uma ferramenta tecnológica capaz de cadastrar os alunos,
                inserindo informações pessoais não requisitadas durante a matrícula no IFRO, mas que é de suma
                importância para equipe responsável por ajudá-los em questões de saúde. Com isso, os alunos responsáveis
                entraram em contato com a servidora que relatou o problema e fizeram alguns questionamentos iniciais,
                entre eles, foi revelado que o SUAP (Ferramenta utilizada para encontrar dados dos alunos), não possui a
                especificidade que é necessária durante a triagem dos estudantes, e por isso começaram a usar o Google
                Forms como forma de suprir o déficit, porém, o mesmo contava com mais de 20 páginas, entre elas,
                perguntas padrão que têm que ser repetidas toda vez já que não há um cadastro.</p>
            <p>Além de trazer um incremento na área da saúde no setor educacional local, a implementação do software
                proposta neste trabalho pode trazer benefícios para outros ambientes escolares futuramente, contribuindo
                para um lugar mais acolhedor e seguro.</p>
            <p>Ademais, outro aspecto que leva ao desenvolvimento desse tema é poder mostrar aos avaliadores,
                aprendizados que a equipe desse projeto obteve ao longo do curso Técnico em Informática, trazendo as
                tecnologias e ferramentas de desenvolvimento e aplicando de forma prática.</p>
        </section>

    </main>
</div>

<script src="<?= URL ?>/js/sobre.js"></script>

<?php include '../App/Views/footer.php'; ?>