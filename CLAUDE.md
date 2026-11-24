# Puncher.com — site estático

Site estático (HTML/CSS/JS) de puncher.com em cinco idiomas: EN na raiz, `de/`, `fr/`, `es/`, `it/`.
O sistema online (`/app/`) é outro projeto e não está neste repositório.
O repositório é publicado direto na pasta pública do site.

## Regras de trabalho

1. **Investigar e relatar antes de alterar.** Ler os arquivos envolvidos, relatar o que foi encontrado e esperar o ok antes de mudar qualquer arquivo.
2. **Apenas ADICIONAR código.** Não alterar lógica existente sem uma decisão consciente, combinada antes.
3. **Versão `?v=` dos arquivos:** ao alterar um CSS/JS, subir o `?v=` em **todas** as páginas que carregam esse arquivo (conferir com `grep`).
4. **Quebras de linha:** os HTML ficam em CRLF na cópia local (`core.autocrlf=true`; o repositório guarda LF).
   - Não usar `sed -i` do Git Bash nos HTML: ele converte para LF. Se usar, restaurar CRLF antes do commit.
   - Antes do commit, conferir com `git diff --stat` / `git show --stat` que cada arquivo mudou só o esperado.
5. **Commit e push:** o Claude Code faz o commit local e **nunca faz push**. O push e a publicação são feitos pelo Almiro.
6. **Nada de dados pessoais ou comerciais** neste repositório, nem em exemplos: nada de nomes de embaixadores, e-mails, telefones ou PayPal. Nos exemplos, usar `?ref=SAMPLE`.
7. **Documentação (`.md`) nunca deve ficar acessível pelo navegador.** A regra 5 do `.htaccess` nega o acesso a qualquer arquivo `.md` (resposta 403). Não remover essa regra.

### Versões atuais (`?v=`)

| Arquivo | Versão | Onde |
|---|---|---|
| `css/style.css` | `2026d` | 5 homes |
| `css/style.css` | `2026c` | demais 42 páginas (mesmo arquivo, query antiga) |
| `js/main.js` | `2026f` | 46 páginas |
| `js/ref-early.js` | `2026a` | 5 homes (no `<head>`) |
| `js/consent.js` | `2026b` | 46 páginas |
| `js/language.js` | `2026b` | 5 homes — inconsistência antiga |
| `js/language.js` | `2026c` | demais 41 páginas |

`terms.html` não carrega nenhum desses scripts. `menu*.html` e `footer*.html` são fragmentos injetados pelo `main.js`.

## Publicação

1. `git push origin main`, feito pelo Almiro.
2. No servidor, `git pull` na pasta do site.
3. Limpar o cache no Site Tools do SiteGround (Speed → Caching → Flush). A limpeza pela linha de comando não funciona.

## Consentimento de cookies (`js/consent.js`)

- **Onde fica a decisão:** `localStorage`, chave `puncher_consent`. Valores:
  - `'granted'`: aceitou;
  - `'denied'`: recusou;
  - ausente: sem decisão, o banner aparece.
- **Aceitar** grava `'granted'` e carrega o GA4. **Recusar** grava `'denied'`.
- **"Cookie settings"** (link no rodapé, `window.openCookieSettings()`): apaga a chave e reabre o banner. A retirada do consentimento só se completa quando o visitante clica em "Decline".
- O `consent.js` não dispara eventos. Outros scripts leem a chave direto do `localStorage`.
- **Caso de borda conhecido (aceito):** se o visitante abre "Cookie settings" e sai sem escolher, a chave fica vazia (sem decisão). Um `puncher_ref` existente é mantido até ele decidir.

## Indicação de embaixador (`?ref=`)

### Leitura e normalização
O código vem do parâmetro `?ref=` da URL. A regra do `normalize()`:
- passa para maiúsculas;
- remove tudo que não seja `A-Z` ou `0-9`;
- corta em 12 caracteres;
- só vale com **3 ou mais** caracteres. Código inválido ou vazio não ativa nada.

> ⚠️ A regra do `normalize()` existe em **dois arquivos**: `js/main.js` (`referral()`) e `js/ref-early.js`. Mantê-las **iguais**.

### Cookie `puncher_ref` (gravado e apagado só pelo `js/main.js`)

**Gravação:** só com `?ref=` válido na URL **e** consentimento:
- ao abrir a página, se `puncher_consent` já for `'granted'`;
- ao clicar em "Accept" na mesma página.

Cada nova visita com `?ref=` e consentimento regrava o cookie e renova o prazo.

**Atributos:** `path=/; max-age=5184000` (60 dias); `SameSite=Lax; Secure`; sem `domain`, só o host `puncher.com`. O `.htaccess` redireciona `www.puncher.com` para `puncher.com`.

**Apagamento** (mesmos atributos, `max-age=0`):
- `referralConsentCleanup()`, em **qualquer** página:
  - ao abrir uma página com `puncher_consent = 'denied'`;
  - no clique em "Decline" do banner, inclusive via "Cookie settings".
- `referral()`: "Decline" numa página com `?ref=`. É redundante, mas inofensivo.
- A string de apagamento aparece duas vezes no `main.js`. Mantê-las iguais e iguais aos atributos da gravação.

Sem decisão de consentimento, nada é gravado nem apagado.

### Repasse do código nos links
Só quando há `?ref=` válido na URL, o `referral()` acrescenta `?ref=CODIGO` a:
- links internos (`puncher.com`, `www.puncher.com` e o host atual);
- links do `/app/`, incluindo `/app/register/`.

Ficam de fora: âncoras da própria página, `mailto:`, `tel:` e links externos. Um `MutationObserver` pega os links do menu e do rodapé, que são injetados depois. O repasse não grava nada.

Sem `?ref=` na URL, os links não são alterados: o `/app/register/` lê o cookie `puncher_ref`. Isso é lógica do app, fora deste repositório.

### Destaque na home (`js/ref-early.js` + `css/style.css`)
- **Carregamento:** `ref-early.js` é carregado de forma **síncrona** no `<head>` das 5 homes, sem `defer` ou `async`. Assim a classe é aplicada antes da primeira pintura e o botão não "pisca".
- **O que ele faz:** aplica o `normalize()` ao `?ref=`. Se não houver, aplica ao cookie `puncher_ref`, **somente leitura**. Com código válido, põe a classe **`has-ref`** no `<html>`.
- **Troca de ordem:** `puncherRefSwap()` é chamada por uma linha inline logo depois do `</div>` de `.hero-buttons`. Com `has-ref`, ela move o link `[data-ref-cta="account"]` para o primeiro lugar **no HTML**, então a ordem visual, do Tab e do leitor de tela coincidem. Não usar `order` no CSS.
- **CSS de `html.has-ref`** (fim do `style.css`): inverte as cores dos botões `[data-ref-cta]` do hero e mostra `.hero-note-ref`.
- **Frase de indicação:** `<strong class="hero-note-ref" hidden>`, no topo do quadro "New here?".
  - EN: You were referred by a Puncher partner.
  - DE: Sie kommen auf Empfehlung eines Puncher-Partners.
  - FR: Vous venez de la part d'un partenaire Puncher.
  - ES: Un socio de Puncher le recomendó nuestro servicio.
  - IT: Sei qui su consiglio di un partner Puncher.
- **O que NÃO muda:** menu, seção `#app`, passo 1 "Create an account", rodapé e todas as outras páginas. O código e o nome do embaixador nunca aparecem na página.

## Roteiro de testes (aba anônima)

1. `https://puncher.com/` sem parâmetro: "Get a free quote" azul e em primeiro, sem frase de indicação.
2. `https://puncher.com/?ref=SAMPLE`: "Create a free account" azul e em primeiro, frase visível, sem piscada. Repetir em `/de/`, `/fr/`, `/es/`, `/it/`.
3. `?ref=ab` (inválido): página normal.
4. Com `?ref=SAMPLE`, aceitar os cookies. Em DevTools → Application → Cookies, o `puncher_ref=SAMPLE` deve existir. Abrir a home sem parâmetro: o destaque continua.
5. Clicar em "Create a free account": abre `/app/register/` com o "Referral code" preenchido.
6. Em `/about.html` (sem `?ref=`), abrir "Cookie settings" e recusar: o `puncher_ref` some na hora. A home seguinte abre normal. Repetir em outro idioma.
7. Nova aba anônima, `?ref=SAMPLE`, recusar direto no banner: nenhum `puncher_ref`.
8. Celular (largura de 360 px): botões empilhados, o de criar conta em cima quando há código.
9. `https://puncher.com/CLAUDE.md` não abre (403). A regra vale também dentro de `/app/` e da pasta de plugins do sistema, que têm documentação `.md` própria: isso é desejado.
   - Depois de publicar **qualquer** mudança no `.htaccess`, abrir imediatamente a home e o `/app/login/` para confirmar que o site responde.
   - Se der erro 500, restaurar o `.htaccess` anterior.

## Histórico da indicação

- `cc759ba`: indicação de embaixador. O `?ref=` é repassado aos links e ao `/app/`, e o cookie `puncher_ref` é gravado com consentimento.
- `07cdd51`: o banner de cookies (5 idiomas) e os Termos §11.4 passam a cobrir o cookie de indicação.
- `db3dbaf`: "Create a free account" em destaque no hero das 5 homes para quem tem código (`ref-early.js`, CSS `has-ref`, frase de indicação).
- `d366997`: a recusa dos cookies apaga o `puncher_ref` em qualquer página (`referralConsentCleanup()`, `main.js?v=2026f`).

## Pendências conhecidas

- `style.css` está com `?v=2026c` em 42 páginas e `2026d` nas homes. Na próxima mudança de CSS, unificar a versão em todas.
- `language.js` está com `?v=2026b` nas homes e `2026c` nas demais (inconsistência antiga). Unificar quando o arquivo for alterado.
- A regra do `normalize()` está duplicada (`main.js` e `ref-early.js`), e a string de apagamento do cookie também (duas vezes no `main.js`).
- O caso de borda do "Cookie settings" aberto sem escolha está descrito acima e foi aceito como está.
- Em 360 px, o H1 da home alemã ("Produktionsfertige") passa um pouco da largura da tela. É anterior a este trabalho e foi observado nos testes.
