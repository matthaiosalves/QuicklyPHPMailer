# QuicklyPHPMailer com Lógica de Filas

Esta versão do QuicklyPHPMailer implementa uma lógica de filas para o envio de e-mails, permitindo o envio assíncrono de mensagens sem sobrecarregar o servidor.

## Visão Geral

A lógica de filas enfileira os e-mails no WordPress como posts personalizados (`email_queue`). A fila processa os e-mails em segundo plano, com tentativas de reenvio configuráveis em caso de falhas.

## Funcionalidades

- **Fila de Envio**: Ao invés de enviar e-mails imediatamente, eles são enfileirados e processados posteriormente.
- **Controle de Tentativas**: Em caso de falha, o sistema reenvia o e-mail até três vezes.
- **Processamento Assíncrono**: A fila é processada em segundo plano para não impactar a experiência do usuário.

## Como Usar

1. **Instalação**: Instale o plugin no diretório de plugins do WordPress.
2. **Configuração**: Configure o PHPMailer com suas credenciais no código (`HOST`, `USERNAME`, `PASSWORD`).
3. **Envio de E-mails**: Utilize o formulário para enviar e-mails que serão enfileirados e processados em segundo plano.

## Estrutura do Código

- `enfileirarEmail`: Função que adiciona e-mails à fila (`email_queue`).
- `processarFilaDeEmails`: Processa a fila, tentando enviar cada e-mail. Em caso de falha, faz até 3 tentativas.
- `sendEmail`: Função de envio de e-mail utilizando o PHPMailer.

## Contribuição

Para contribuir:

1. Abra uma _Issue_ para discutir a proposta.
2. Faça um _fork_ e crie uma nova _branch_ para sua feature.
3. Abra um _Pull Request_ para revisão.

## Observações

- Esta versão estável (v1.5) inclui a lógica de filas para melhorar a performance e reduzir a duplicidade de e-mails.
