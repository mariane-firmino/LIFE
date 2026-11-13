<?php //abertura da tag de php
class checa
{ // criando a classe checa
    // Iniciais do primeiro e do segundo nome (ex.: "Ana Beatriz Souza" -> "AB").
    // Pula "da", "de", "do", "das", "dos" e "e" ("Maria da Silva" -> "MS").
    public static function iniciais($nome)
    {
        $partes = preg_split('/\s+/u', trim((string) $nome), -1, PREG_SPLIT_NO_EMPTY);
        $partes = array_values(array_filter($partes, function ($p) {
            return !in_array(mb_strtolower($p), ['da', 'de', 'do', 'das', 'dos', 'e'], true);
        }));

        $iniciais = '';
        foreach (array_slice($partes, 0, 2) as $p) {
            $iniciais .= mb_substr($p, 0, 1);
        }

        return $iniciais !== '' ? mb_strtoupper($iniciais) : '?';
    }

    public static function checarNome($nome)
    {
        // Só letras (com ou sem acento) e um espaço entre as palavras.
        // Não aceita números nem caracteres especiais (ex.: _ ^ [ ] @ #).
        // (A regra antiga usava "A-z", que deixava passar _ ^ [ ] ` \.)
        if (!preg_match('/^\p{L}+(?: \p{L}+)*$/u', (string) $nome)):
            return true; // Retorna TRUE se o nome for INVALIDO
        else:
            return false; // Retorna FALSE se o nome foi VÁLIDO
        endif;
    } // fim da função checarNome

    public static function checarEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)):
            return true; // Retorna TRUE se houver ERRO no email
        else:
            return false; // Retorna FALSE se o email for VÁLIDO
        endif;
    } // fim da função checarEmail

    public static function checarCpf($cpf)
    {
        if (!preg_match('/^[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}$/', $cpf)):
            return true; // Retorna TRUE se houver ERRO no CPF
        else:
            return false; // Retorna FALSE se o CPF for VÁLIDO
        endif;
    } // fim da função checarCpf

    public static function dataBr($data)
    {
        if (isset($data)):
            return date('d/m/Y H:i', strtotime($data)); // converte a string de data para o formato BR
        else:
            return false;
        endif;
    } // fim da funçao dataBr
} // fim da classe
