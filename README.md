# 🎮 Jogo da Memória - Games

Um jogo da memória divertido e interativo com tema de jogos famosos! Desafie seus amigos para ver quem tem a melhor memória.

## 📋 Características

- **Tema de Games**: Cards com jogos populares como GTA-V, Minecraft, CS:GO e mais
- **Ratings**: Cada jogo tem sua própria avaliação (83-95)
- **Modo Multiplayer**: Jogue com um amigo em turnos alternados
- **Efeitos Visuais**: 
  - Preview inicial das cartas por 1 segundo
  - Animação de vitória com fogos de artifício
  - Interface responsiva e moderna
- **Personalização**: 
  - Modo claro/escuro
  - Configurável através do painel admin
- **Opções de Jogo**: 
  - 6 pares (12 cards)
  - 8 pares (16 cards)
  - 10 pares (20 cards)
  - 12 pares (24 cards)
  - 16 pares (32 cards)

## 🎯 Como Jogar

1. **Configuração Inicial**:
   - Escolha a quantidade de pares de cards
   - Defina a imagem do verso das cartas
   - Selecione as imagens dos jogos

2. **Iniciando o Jogo**:
   - Digite o nome dos dois jogadores
   - Clique em "Começar Jogo"
   - Observe todas as cartas por 1 segundo

3. **Durante o Jogo**:
   - Clique em duas cartas para tentar formar um par
   - Se acertar, você ganha um ponto e continua jogando
   - Se errar, a vez passa para o outro jogador
   - Continue até encontrar todos os pares

4. **Vitória**:
   - O jogador com mais pontos vence
   - Uma celebração com fogos de artifício será exibida
   - O nome do vencedor aparecerá em destaque

## 🛠️ Requisitos Técnicos

- Servidor web (XAMPP, WAMP, etc.)
- PHP 7.0 ou superior
- MySQL/MariaDB
- Navegador moderno com suporte a JavaScript

## 📦 Instalação

1. Clone este repositório para sua pasta web:
   ```bash
   git clone [URL_DO_REPOSITORIO]
   ```

2. Configure o banco de dados:
   - Importe o arquivo `db_setup.sql`
   - Ajuste as credenciais em `db_config.php`

3. Acesse através do navegador:
   ```
   http://localhost/Jogo
   ```

## 🎨 Personalização

### Modo Admin
- Acesse o painel admin através do botão ⚙️
- Gerencie imagens e configurações do jogo
- Altere o tema e aparência

### Configurações do Jogo
- Modifique `config.php` para ajustes básicos
- Personalize estilos em `styles.css`
- Ajuste comportamentos em `script.js`

## 🔒 Segurança

- Validação de entrada de dados
- Proteção contra XSS
- Sanitização de nomes de arquivos
- Verificação de tipos de imagem

## 🤝 Contribuição

Sinta-se à vontade para contribuir com o projeto:
1. Faça um Fork
2. Crie sua Feature Branch
3. Faça um Commit com suas mudanças
4. Envie um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 👥 Autores

- Fabio Regis - Desenvolvedor Principal

## 🙏 Agradecimentos

- Codeium por fornecer a assistência AI
- Todos os contribuidores e testadores
