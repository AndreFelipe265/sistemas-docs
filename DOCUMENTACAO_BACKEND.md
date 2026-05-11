# Documentacao completa do back-end Laravel

Projeto: `sistema-docs`  
Tipo: API REST em Laravel para o sistema de documentos Bonsae  
Front-end conectado: `bonsae-documentos-main`  
URL local do back-end: `http://127.0.0.1:8000`  
Prefixo das rotas da API: `http://127.0.0.1:8000/api`

---

## 1. Objetivo deste documento

Este arquivo foi criado para voce estudar o back-end com calma e entender:

- Onde cada parte do Laravel fica.
- Como o front-end conversa com o back-end.
- Como funciona o login com token.
- Como o banco de dados esta organizado.
- Como clientes, templates e documentos sao salvos.
- Como o sistema impede que um usuario veja dados de outro usuario.
- Como rodar, testar e debugar a aplicacao.

A ideia e que voce consiga abrir o projeto e saber o que cada arquivo importante faz.

---

## 2. Onde esta o back-end no PC

O projeto Laravel fica em:

```text
C:\Users\richa\OneDrive\Área de Trabalho\sistema-docs
```

O front-end React fica em:

```text
C:\Users\richa\OneDrive\Área de Trabalho\bonsae-documentos-main
```

O front chama o back neste endereco:

```text
http://127.0.0.1:8000/api
```

---

## 3. Como rodar tudo

Use dois terminais.

### 3.1. Terminal do back-end

Antes, abra o XAMPP e ligue o MySQL.

Depois:

```powershell
cd "C:\Users\richa\OneDrive\Área de Trabalho\sistema-docs"
php artisan serve --host=127.0.0.1 --port=8000
```

O Laravel deve ficar em:

```text
http://127.0.0.1:8000
```

### 3.2. Terminal do front-end

```powershell
cd "C:\Users\richa\OneDrive\Área de Trabalho\bonsae-documentos-main"
npm run dev
```

O front deve ficar em:

```text
http://127.0.0.1:8080
```

---

## 4. Tecnologias usadas no back-end

O arquivo `composer.json` mostra as dependencias principais:

```json
"laravel/framework": "^10.10",
"laravel/sanctum": "^3.3",
"laravel/tinker": "^2.8"
```

Na pratica:

- **Laravel**: framework PHP que organiza rotas, controllers, models, banco, validacao etc.
- **Eloquent ORM**: camada do Laravel para trabalhar com tabelas como se fossem classes PHP.
- **Sanctum**: pacote do Laravel usado para autenticar via token.
- **MySQL**: banco de dados usado pelo projeto.
- **Artisan**: ferramenta de linha de comando do Laravel.

---

## 5. Configuracao do banco

O arquivo `.env` do projeto define a conexao com o banco.

Configuracao usada:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistemas_documentos
DB_USERNAME=root
DB_PASSWORD=
```

Isto significa:

- O Laravel usa MySQL.
- O MySQL esta rodando localmente.
- O banco se chama `sistemas_documentos`.
- O usuario e `root`.
- A senha esta vazia, padrao comum em ambiente local com XAMPP.

Importante: o arquivo `.env` nao deve ser publicado em repositorios publicos, porque pode conter senhas e chaves.

---

## 6. Estrutura principal do Laravel

Arquivos e pastas mais importantes neste projeto:

```text
sistema-docs/
  app/
    Http/
      Controllers/
        AuthController.php
        ClienteController.php
        TemplateController.php
        DocumentoController.php
    Models/
      User.php
      Cliente.php
      Template.php
      Documento.php
  routes/
    api.php
    web.php
  database/
    migrations/
  config/
    auth.php
    sanctum.php
    cors.php
    database.php
  .env
  artisan
  composer.json
```

### 6.1. `routes/api.php`

Define as URLs da API.

Exemplo:

```php
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/clientes', [ClienteController::class, 'index']);
```

Quando o front faz uma chamada para:

```text
GET http://127.0.0.1:8000/api/clientes
```

o Laravel procura em `routes/api.php` qual controller deve responder.

### 6.2. `app/Http/Controllers`

Controllers recebem as requisicoes HTTP e decidem o que fazer.

Exemplo:

- Validar dados enviados pelo front.
- Consultar o banco.
- Criar registros.
- Atualizar registros.
- Devolver JSON.

### 6.3. `app/Models`

Models representam tabelas do banco.

Exemplo:

- `User` representa a tabela `users`.
- `Cliente` representa a tabela `clientes`.
- `Template` representa a tabela `templates`.
- `Documento` representa a tabela `documentos`.

### 6.4. `database/migrations`

Migrations sao arquivos que descrevem a estrutura do banco.

Elas dizem:

- Crie uma tabela.
- Adicione uma coluna.
- Crie uma chave estrangeira.
- Remova algo se fizer rollback.

### 6.5. `config/cors.php`

Controla se o navegador pode chamar a API a partir de outro endereco.

Como o front roda em `http://127.0.0.1:8080` e o back em `http://127.0.0.1:8000`, o navegador considera isso como origens diferentes. O CORS precisa permitir essa comunicacao.

Hoje esta configurado de forma aberta em desenvolvimento:

```php
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

Isso facilita localmente. Em producao, normalmente voce restringe para o dominio real do front.

---

## 7. Fluxo geral de uma requisicao

Exemplo: o usuario abre a tela de clientes no front.

1. O front chama:

```text
GET /api/clientes
```

2. O Laravel olha `routes/api.php`.

3. A rota esta protegida por:

```php
Route::middleware('auth:sanctum')->group(...)
```

4. O Sanctum verifica o token enviado no header:

```http
Authorization: Bearer TOKEN_AQUI
```

5. Se o token for valido, Laravel descobre quem e o usuario:

```php
$request->user()
```

6. O `ClienteController@index` busca apenas clientes daquele usuario:

```php
Cliente::where('user_id', $request->user()->id)->latest()->get()
```

7. O Laravel devolve JSON.

8. O front renderiza a lista.

---

## 8. Autenticacao

Arquivo principal:

```text
app/Http/Controllers/AuthController.php
```

Rotas:

```text
POST /api/auth/register
POST /api/auth/login
GET  /api/auth/me
POST /api/auth/logout
```

### 8.1. Cadastro

Rota:

```text
POST /api/auth/register
```

Payload esperado:

```json
{
  "name": "Richard",
  "email": "richard@email.com",
  "password": "123456"
}
```

O controller valida:

```php
'name' => 'required|string|max:255',
'email' => 'required|email|max:255|unique:users,email',
'password' => 'required|string|min:6',
```

Significado:

- `name` e obrigatorio, texto, maximo 255 caracteres.
- `email` e obrigatorio, precisa parecer email, deve ser unico na tabela `users`.
- `password` e obrigatorio, texto, minimo 6 caracteres.

Depois cria o usuario:

```php
$user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
]);
```

Importante:

- A senha nao e salva em texto puro.
- `Hash::make()` transforma a senha em hash seguro.
- O login depois usa `Hash::check()` para comparar.

Depois gera um token:

```php
$user->createToken('bonsae-front')->plainTextToken
```

Resposta:

```json
{
  "user": {
    "id": 1,
    "name": "Richard",
    "email": "richard@email.com"
  },
  "token": "TOKEN_GERADO_PELO_SANCTUM",
  "token_type": "Bearer"
}
```

### 8.2. Login

Rota:

```text
POST /api/auth/login
```

Payload:

```json
{
  "email": "richard@email.com",
  "password": "123456"
}
```

Fluxo:

1. Valida email e senha.
2. Busca usuario pelo email.
3. Confere a senha:

```php
Hash::check($data['password'], $user->password)
```

4. Se estiver correto, cria token.
5. Se estiver errado, retorna erro 422:

```json
{
  "message": "Credenciais invalidas.",
  "errors": {
    "email": ["Credenciais invalidas."]
  }
}
```

### 8.3. Usuario logado

Rota:

```text
GET /api/auth/me
```

Precisa do header:

```http
Authorization: Bearer TOKEN
```

Retorna o usuario dono daquele token.

### 8.4. Logout

Rota:

```text
POST /api/auth/logout
```

O controller faz:

```php
$request->user()?->currentAccessToken()?->delete();
```

Isso apaga o token atual da tabela `personal_access_tokens`.

Depois disso, o token nao deve mais funcionar.

---

## 9. Sanctum e a tabela de tokens

O Sanctum salva tokens na tabela:

```text
personal_access_tokens
```

Colunas principais:

| Coluna | O que significa |
| --- | --- |
| `id` | ID do token |
| `tokenable_type` | Classe dona do token, geralmente `App\Models\User` |
| `tokenable_id` | ID do usuario dono do token |
| `name` | Nome do token, aqui `bonsae-front` |
| `token` | Hash do token |
| `abilities` | Permissoes extras, se usadas |
| `last_used_at` | Ultima vez em que o token foi usado |
| `expires_at` | Data de expiracao, se configurada |
| `created_at` | Criacao |
| `updated_at` | Atualizacao |

O front nunca deve tentar ler essa tabela diretamente. Ele recebe o token no login/cadastro e manda esse token nas chamadas seguintes.

---

## 10. Rotas da API

Todas as rotas de `clientes`, `templates` e `documentos` estao protegidas por `auth:sanctum`.

Isso quer dizer: sem token, retorna `401 Unauthorized`.

### 10.1. Rotas publicas

| Metodo | URL | Controller | Objetivo |
| --- | --- | --- | --- |
| POST | `/api/auth/register` | `AuthController@register` | Criar usuario |
| POST | `/api/auth/login` | `AuthController@login` | Fazer login |

### 10.2. Rotas protegidas de autenticacao

| Metodo | URL | Controller | Objetivo |
| --- | --- | --- | --- |
| GET | `/api/auth/me` | `AuthController@me` | Ver usuario logado |
| POST | `/api/auth/logout` | `AuthController@logout` | Apagar token atual |
| GET | `/api/user` | closure em `api.php` | Rota padrao Laravel para usuario logado |

### 10.3. Rotas de clientes

| Metodo | URL | Controller | Objetivo |
| --- | --- | --- | --- |
| GET | `/api/clientes` | `ClienteController@index` | Listar clientes do usuario |
| GET | `/api/clientes/{id}` | `ClienteController@show` | Ver um cliente do usuario |
| POST | `/api/clientes` | `ClienteController@store` | Criar cliente |
| PUT | `/api/clientes/{id}` | `ClienteController@update` | Atualizar cliente |
| DELETE | `/api/clientes/{id}` | `ClienteController@destroy` | Excluir cliente |

### 10.4. Rotas de templates

| Metodo | URL | Controller | Objetivo |
| --- | --- | --- | --- |
| GET | `/api/templates` | `TemplateController@index` | Listar templates do usuario |
| GET | `/api/templates/{id}` | `TemplateController@show` | Ver um template do usuario |
| POST | `/api/templates` | `TemplateController@store` | Criar template |
| PUT | `/api/templates/{id}` | `TemplateController@update` | Atualizar template |
| DELETE | `/api/templates/{id}` | `TemplateController@destroy` | Excluir template |

### 10.5. Rotas de documentos

| Metodo | URL | Controller | Objetivo |
| --- | --- | --- | --- |
| GET | `/api/documentos` | `DocumentoController@index` | Listar documentos do usuario |
| GET | `/api/documentos/{id}` | `DocumentoController@show` | Ver um documento do usuario |
| POST | `/api/documentos` | `DocumentoController@store` | Criar documento |
| PUT | `/api/documentos/{id}` | `DocumentoController@update` | Atualizar documento inteiro |
| PATCH | `/api/documentos/{id}` | `DocumentoController@update` | Atualizar parcialmente |
| DELETE | `/api/documentos/{id}` | `DocumentoController@destroy` | Excluir documento |

---

## 11. Banco de dados

Banco usado:

```text
sistemas_documentos
```

Tabelas importantes:

```text
users
personal_access_tokens
clientes
templates
documentos
```

---

## 12. Tabela `users`

Guarda os usuarios que fazem login.

Colunas:

| Coluna | Tipo | Obrigatoria | Explicacao |
| --- | --- | --- | --- |
| `id` | bigint unsigned | sim | ID do usuario |
| `name` | varchar(255) | sim | Nome |
| `email` | varchar(255) | sim | Email unico |
| `email_verified_at` | timestamp | nao | Confirmacao de email, nao usada agora |
| `password` | varchar(255) | sim | Hash da senha |
| `remember_token` | varchar(100) | nao | Recurso padrao Laravel |
| `created_at` | timestamp | nao | Criado em |
| `updated_at` | timestamp | nao | Atualizado em |

Model:

```text
app/Models/User.php
```

Pontos importantes do model:

```php
use HasApiTokens, HasFactory, Notifiable;
```

`HasApiTokens` vem do Sanctum e permite:

```php
$user->createToken(...)
```

O model tambem esconde campos sensiveis:

```php
protected $hidden = [
    'password',
    'remember_token',
];
```

Isso evita que o JSON da API devolva a senha.

---

## 13. Tabela `clientes`

Guarda clientes cadastrados por cada usuario.

Colunas atuais do banco:

| Coluna | Tipo | Obrigatoria | Explicacao |
| --- | --- | --- | --- |
| `id` | bigint unsigned | sim | ID do cliente |
| `user_id` | bigint unsigned | nao no banco atual | Usuario dono do cliente |
| `nome` | varchar(255) | sim | Nome do cliente |
| `cpf` | varchar(20) | nao | CPF |
| `email` | varchar(255) | nao | Email |
| `telefone` | varchar(255) | nao | Telefone |
| `endereco` | varchar(255) | nao | Endereco |
| `cidade` | varchar(255) | nao | Cidade |
| `estado` | varchar(2) | nao | UF |
| `cep` | varchar(255) | nao | CEP |
| `created_at` | timestamp | nao | Criado em |
| `updated_at` | timestamp | nao | Atualizado em |

Relacionamento:

```text
clientes.user_id -> users.id
```

No banco atual, existe foreign key com cascade:

```text
clientes_user_id_foreign
ON DELETE CASCADE
```

Significa: se um usuario for apagado, os clientes dele tambem sao apagados.

Model:

```text
app/Models/Cliente.php
```

Campos liberados para criacao em massa:

```php
protected $fillable = [
    'user_id',
    'nome',
    'cpf',
    'email',
    'telefone',
    'endereco',
    'cidade',
    'estado',
    'cep',
];
```

Por que `$fillable` importa?

O Laravel protege contra mass assignment. Se voce usar:

```php
Cliente::create($data);
```

so os campos dentro de `$fillable` podem ser gravados.

Relacao com usuario:

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

Isto diz: cada cliente pertence a um usuario.

---

## 14. Tabela `templates`

Guarda modelos de documentos.

Colunas:

| Coluna | Tipo | Obrigatoria | Explicacao |
| --- | --- | --- | --- |
| `id` | bigint unsigned | sim | ID do template |
| `user_id` | bigint unsigned | nao no banco atual | Usuario dono do template |
| `titulo` | varchar(255) | sim | Titulo do template |
| `conteudo` | longtext | sim | HTML/conteudo do template |
| `background_image` | varchar(255) | nao | Imagem/timbrado/fundo |
| `created_at` | timestamp | nao | Criado em |
| `updated_at` | timestamp | nao | Atualizado em |

Relacionamento:

```text
templates.user_id -> users.id
```

Model:

```text
app/Models/Template.php
```

Campos liberados:

```php
protected $fillable = [
    'user_id',
    'titulo',
    'conteudo',
    'background_image',
];
```

Relacao:

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

---

## 15. Tabela `documentos`

Guarda documentos finais criados a partir de um template e de um cliente.

Colunas:

| Coluna | Tipo | Obrigatoria | Explicacao |
| --- | --- | --- | --- |
| `id` | bigint unsigned | sim | ID do documento |
| `user_id` | bigint unsigned | nao no banco atual | Usuario dono do documento |
| `cliente_id` | bigint unsigned | sim | Cliente usado no documento |
| `template_id` | bigint unsigned | sim | Template usado no documento |
| `titulo` | varchar(255) | sim | Titulo do documento |
| `conteudo_final` | longtext | sim | HTML final salvo |
| `status` | varchar(255) | sim | Status, padrao `rascunho` |
| `created_at` | timestamp | nao | Criado em |
| `updated_at` | timestamp | nao | Atualizado em |

Relacionamentos:

```text
documentos.user_id -> users.id
documentos.cliente_id -> clientes.id
documentos.template_id -> templates.id
```

No banco atual:

```text
documentos_cliente_id_foreign ON DELETE CASCADE
documentos_template_id_foreign ON DELETE CASCADE
documentos_user_id_foreign ON DELETE CASCADE
```

Significa:

- Se apagar um cliente, documentos ligados a ele sao apagados.
- Se apagar um template, documentos ligados a ele sao apagados.
- Se apagar um usuario, documentos dele sao apagados.

Model:

```text
app/Models/Documento.php
```

Campos liberados:

```php
protected $fillable = [
    'user_id',
    'cliente_id',
    'template_id',
    'titulo',
    'conteudo_final',
    'status',
];
```

Relacoes:

```php
public function user()
{
    return $this->belongsTo(User::class);
}

public function cliente()
{
    return $this->belongsTo(Cliente::class);
}

public function template()
{
    return $this->belongsTo(Template::class);
}
```

Assim o Laravel consegue carregar:

```php
Documento::with(['cliente', 'template'])->get();
```

Isso retorna documentos junto com seus dados relacionados.

---

## 16. Diagrama simples do banco

```text
users
  id
  name
  email
  password

  1 usuario tem muitos clientes
  1 usuario tem muitos templates
  1 usuario tem muitos documentos

clientes
  id
  user_id -> users.id
  nome
  cpf
  email
  telefone
  endereco
  cidade
  estado
  cep

templates
  id
  user_id -> users.id
  titulo
  conteudo
  background_image

documentos
  id
  user_id -> users.id
  cliente_id -> clientes.id
  template_id -> templates.id
  titulo
  conteudo_final
  status
```

Visualizando relacoes:

```text
User 1 ---- N Cliente
User 1 ---- N Template
User 1 ---- N Documento

Cliente 1 ---- N Documento
Template 1 ---- N Documento
```

---

## 17. Isolamento por usuario

Este foi um ponto importante corrigido.

Antes, o controller fazia algo como:

```php
Cliente::all();
```

Isso lista todos os clientes do banco, de todos os usuarios.

Agora faz:

```php
Cliente::where('user_id', $request->user()->id)->latest()->get();
```

Isto lista somente clientes cujo `user_id` e igual ao ID do usuario autenticado.

### 17.1. Por que isso e importante?

Imagine:

```text
Usuario A tem id 10
Usuario B tem id 11
```

Clientes:

| id | user_id | nome |
| --- | --- | --- |
| 1 | 10 | Cliente do A |
| 2 | 11 | Cliente do B |

Quando o usuario A chama:

```text
GET /api/clientes
```

o back executa:

```php
where('user_id', 10)
```

Resultado:

```text
Cliente do A
```

Quando o usuario B chama:

```php
where('user_id', 11)
```

Resultado:

```text
Cliente do B
```

Um usuario nao ve dados do outro.

### 17.2. Acesso direto por ID tambem e bloqueado

Nao basta proteger a lista. Tambem precisa proteger:

```text
GET /api/clientes/1
```

Porque se o usuario B souber ou chutar o ID `1`, ele poderia tentar acessar o cliente do usuario A.

Por isso o `show` faz:

```php
$cliente = Cliente::where('user_id', $request->user()->id)->find($id);
```

Se o cliente existe mas pertence a outro usuario, essa consulta nao encontra nada e retorna:

```json
{
  "message": "Cliente nao encontrado"
}
```

Status:

```text
404
```

Isso e intencional.

---

## 18. Controller de clientes

Arquivo:

```text
app/Http/Controllers/ClienteController.php
```

### 18.1. `index`

Lista clientes do usuario autenticado.

```php
Cliente::where('user_id', $request->user()->id)->latest()->get()
```

Conceitos:

- `$request->user()` vem do Sanctum.
- `where('user_id', ...)` filtra pelo dono.
- `latest()` ordena pelos mais recentes.
- `get()` executa a consulta.

### 18.2. `show`

Busca um cliente especifico, mas somente se ele pertencer ao usuario logado.

Se nao achar:

```php
return response()->json(['message' => 'Cliente nao encontrado'], 404);
```

### 18.3. `store`

Cria cliente.

Validacao:

```php
'nome' => 'required|string|max:255',
'cpf' => 'nullable|string|max:20',
'email' => 'nullable|email|max:255',
'telefone' => 'nullable|string|max:30',
'endereco' => 'nullable|string|max:255',
'cidade' => 'nullable|string|max:255',
'estado' => 'nullable|string|max:2',
'cep' => 'nullable|string|max:20',
```

Depois grava:

```php
$cliente = Cliente::create([
    ...$data,
    'user_id' => $request->user()->id,
]);
```

O operador `...$data` espalha os campos validados no array.

Exemplo:

```php
$data = [
    'nome' => 'Joao',
    'email' => 'joao@email.com'
];
```

Com:

```php
[
    ...$data,
    'user_id' => 10,
]
```

vira:

```php
[
    'nome' => 'Joao',
    'email' => 'joao@email.com',
    'user_id' => 10,
]
```

### 18.4. `update`

Atualiza um cliente somente se ele for do usuario logado.

Usa:

```php
'nome' => 'sometimes|required|string|max:255'
```

`sometimes` significa: se o campo vier na requisicao, valide. Se nao vier, tudo bem.

### 18.5. `destroy`

Apaga cliente somente se ele for do usuario logado.

---

## 19. Controller de templates

Arquivo:

```text
app/Http/Controllers/TemplateController.php
```

Funciona como o controller de clientes, mas para templates.

Campos:

```php
'titulo' => 'required|string|max:255',
'conteudo' => 'required|string',
'background_image' => 'nullable|string',
```

Quando cria:

```php
$template = Template::create([
    ...$data,
    'user_id' => $request->user()->id,
]);
```

Assim cada template fica ligado ao usuario que criou.

---

## 20. Controller de documentos

Arquivo:

```text
app/Http/Controllers/DocumentoController.php
```

Este controller e mais sensivel porque documento depende de:

- usuario
- cliente
- template

### 20.1. Listagem

```php
Documento::with(['cliente', 'template'])
    ->where('user_id', $request->user()->id)
    ->latest()
    ->get()
```

O `with(['cliente', 'template'])` carrega dados relacionados.

Sem `with`, o documento viria assim:

```json
{
  "id": 1,
  "cliente_id": 5,
  "template_id": 8,
  "titulo": "Contrato"
}
```

Com `with`, pode vir assim:

```json
{
  "id": 1,
  "cliente_id": 5,
  "template_id": 8,
  "titulo": "Contrato",
  "cliente": {
    "id": 5,
    "nome": "Cliente Exemplo"
  },
  "template": {
    "id": 8,
    "titulo": "Template Exemplo"
  }
}
```

Isso facilita o front.

### 20.2. Criacao

Validacao principal:

```php
'cliente_id' => [
    'required',
    Rule::exists('clientes', 'id')->where('user_id', $userId),
],
'template_id' => [
    'required',
    Rule::exists('templates', 'id')->where('user_id', $userId),
],
```

Isto e muito importante.

Nao basta verificar se o `cliente_id` existe. Ele precisa existir e pertencer ao mesmo usuario.

Sem isso, o usuario B poderia criar documento usando `cliente_id` do usuario A.

Com isso, se o usuario B tentar usar cliente/template de outro usuario, o Laravel retorna `422`.

### 20.3. Status padrao

Na criacao:

```php
'status' => $data['status'] ?? 'rascunho',
```

Se o front nao enviar status, salva `rascunho`.

### 20.4. Atualizacao

Tambem valida `cliente_id` e `template_id` pelo usuario.

Isso impede que um documento existente seja editado para apontar para dados de outro usuario.

### 20.5. Exclusao

So apaga se o documento pertencer ao usuario autenticado.

---

## 21. `routes/api.php` em detalhes

O arquivo separa rotas publicas e rotas protegidas.

### 21.1. Rotas publicas

```php
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});
```

Cadastro e login nao exigem token, porque o usuario ainda nao tem token.

Mas `me` e `logout` exigem token.

### 21.2. Rotas protegidas

```php
Route::middleware('auth:sanctum')->group(function () {
    ...
});
```

Tudo dentro desse bloco exige token.

Isso protege:

- `/api/clientes`
- `/api/templates`
- `/api/documentos`
- `/api/user`

Se chamar sem token:

```json
{
  "message": "Unauthenticated."
}
```

Status:

```text
401
```

---

## 22. Como o front deve chamar a API

O front usa `fetch`.

Depois do login, ele guarda o token no navegador e manda:

```http
Authorization: Bearer TOKEN
Accept: application/json
Content-Type: application/json
```

Exemplo de chamada:

```http
GET /api/clientes
Authorization: Bearer 1|abcdef...
Accept: application/json
```

Sem `Authorization`, as rotas protegidas nao funcionam.

---

## 23. Exemplos de teste manual

Voce pode testar com PowerShell.

### 23.1. Registrar usuario

```powershell
$body = @{
  name = "Usuario Teste"
  email = "teste@example.com"
  password = "123456"
} | ConvertTo-Json

$auth = Invoke-RestMethod `
  -Uri "http://127.0.0.1:8000/api/auth/register" `
  -Method POST `
  -ContentType "application/json" `
  -Headers @{ Accept = "application/json" } `
  -Body $body

$auth
```

### 23.2. Guardar token

```powershell
$headers = @{
  Accept = "application/json"
  Authorization = "Bearer $($auth.token)"
}
```

### 23.3. Criar cliente

```powershell
$clienteBody = @{
  nome = "Cliente de Teste"
  cpf = "00000000000"
  email = "cliente@example.com"
} | ConvertTo-Json

$cliente = Invoke-RestMethod `
  -Uri "http://127.0.0.1:8000/api/clientes" `
  -Method POST `
  -ContentType "application/json" `
  -Headers $headers `
  -Body $clienteBody

$cliente
```

### 23.4. Listar clientes

```powershell
Invoke-RestMethod `
  -Uri "http://127.0.0.1:8000/api/clientes" `
  -Method GET `
  -Headers $headers
```

### 23.5. Testar bloqueio sem token

```powershell
Invoke-RestMethod `
  -Uri "http://127.0.0.1:8000/api/clientes" `
  -Method GET `
  -Headers @{ Accept = "application/json" }
```

Esperado:

```text
401 Unauthorized
```

---

## 24. Codigos HTTP que aparecem

| Codigo | Significado | Quando aparece |
| --- | --- | --- |
| 200 | OK | Listagem, login, consulta bem-sucedida |
| 201 | Created | Criou usuario, cliente, template ou documento |
| 401 | Unauthorized | Sem token ou token invalido |
| 404 | Not Found | Registro nao existe ou pertence a outro usuario |
| 422 | Validation Error | Dados invalidos, senha errada, cliente/template de outro usuario |
| 500 | Server Error | Erro interno no Laravel |

Um detalhe importante: quando o usuario tenta acessar dado de outro usuario, retornar `404` e uma boa pratica. Assim a API nem confirma que aquele ID existe.

---

## 25. Validacao

O Laravel valida dados com:

```php
$data = $request->validate([...]);
```

Se passar, `$data` recebe os campos validados.

Se falhar, Laravel para ali e retorna JSON com erro `422`.

Exemplo de erro:

```json
{
  "message": "The nome field is required.",
  "errors": {
    "nome": ["The nome field is required."]
  }
}
```

O front pode mostrar `message` ou o primeiro item de `errors`.

---

## 26. Eloquent ORM

Eloquent permite trabalhar com tabelas usando classes.

Exemplos:

```php
Cliente::create([...]);
Cliente::where('user_id', 1)->get();
Cliente::where('user_id', 1)->find(10);
```

Sem Eloquent, voce escreveria SQL manual:

```sql
SELECT * FROM clientes WHERE user_id = 1;
```

Com Eloquent, o Laravel monta SQL para voce.

### 26.1. `find`

```php
Cliente::find($id)
```

Busca pela chave primaria `id`.

### 26.2. `where`

```php
Cliente::where('user_id', $request->user()->id)
```

Adiciona condicao.

### 26.3. `latest`

```php
latest()
```

Ordena do mais recente para o mais antigo, geralmente por `created_at`.

### 26.4. `get`

Executa a consulta e retorna uma colecao.

### 26.5. `create`

Cria um registro.

Precisa dos campos em `$fillable`.

### 26.6. `update`

Atualiza um registro existente.

### 26.7. `delete`

Apaga um registro.

---

## 27. Cascata no banco

Algumas relacoes tem `ON DELETE CASCADE`.

Isso quer dizer:

```text
Se apagar o registro pai, apaga tambem os filhos.
```

Exemplo:

- Usuario tem clientes.
- Cliente tem documentos.

Se apagar um cliente:

- Documentos daquele cliente sao apagados.

Se apagar um usuario:

- Clientes, templates e documentos ligados a esse usuario podem ser apagados por cascade.

Isso e poderoso, mas precisa ser usado com cuidado.

---

## 28. Ponto de atencao: migrations vs banco atual

O banco atual possui `user_id` em:

```text
clientes
templates
documentos
```

E tambem possui foreign keys para `users`.

Porem, as migrations visiveis nesta pasta nao documentam completamente essas colunas `user_id`.

Foi verificado pelo banco atual que as tabelas existem assim:

```text
clientes.user_id -> users.id
templates.user_id -> users.id
documentos.user_id -> users.id
```

Mas as migrations antigas em `database/migrations` estao desorganizadas. Por exemplo:

- `create_clientes_table` ja cria campos como `nome`, `cpf`, etc.
- `add_campos_to_clientes_table` tenta adicionar campos parecidos de novo.
- As colunas `user_id` existem no banco, mas nao aparecem claramente em uma migration limpa nesta pasta.

### 28.1. Por que isso importa?

Se voce apagar o banco e rodar:

```powershell
php artisan migrate:fresh
```

pode ser que o banco recriado nao fique igual ao banco atual, ou que migration antiga falhe por tentar criar coluna duplicada.

### 28.2. Melhor melhoria futura

Criar migrations novas e limpas para garantir:

- `clientes.user_id`
- `templates.user_id`
- `documentos.user_id`

E depois, quando todos os registros antigos tiverem dono:

- tornar `user_id` obrigatorio (`NOT NULL`).

Hoje, o app ja grava `user_id` sempre que cria dados novos.

---

## 29. Como debugar

### 29.1. Ver rotas

```powershell
php artisan route:list --path=api
```

Mostra todas as rotas da API.

### 29.2. Ver status das migrations

```powershell
php artisan migrate:status
```

Mostra quais migrations rodaram.

### 29.3. Limpar caches do Laravel

```powershell
php artisan optimize:clear
```

Use quando alterar rotas/configs e algo parecer preso.

### 29.4. Ver logs

```text
storage/logs/laravel.log
```

Quando der erro 500, esse arquivo costuma dizer o motivo real.

### 29.5. Testar PHP de um arquivo

```powershell
php -l app\Http\Controllers\ClienteController.php
```

Se retornar:

```text
No syntax errors detected
```

o arquivo nao tem erro de sintaxe.

---

## 30. Fluxos completos do sistema

### 30.1. Criar usuario e entrar

1. Front manda `POST /api/auth/register`.
2. Back cria usuario em `users`.
3. Back cria token em `personal_access_tokens`.
4. Front salva token no navegador.
5. Todas as chamadas seguintes usam `Authorization: Bearer TOKEN`.

### 30.2. Criar cliente

1. Usuario esta logado.
2. Front manda `POST /api/clientes`.
3. Sanctum identifica usuario.
4. Controller valida os campos.
5. Back salva cliente com `user_id` do usuario logado.
6. Cliente aparece somente para esse usuario.

### 30.3. Criar template

1. Usuario esta logado.
2. Front manda `POST /api/templates`.
3. Back valida `titulo`, `conteudo`, `background_image`.
4. Back salva template com `user_id`.
5. Template aparece somente para esse usuario.

### 30.4. Criar documento

1. Usuario esta logado.
2. Front escolhe cliente e template.
3. Front manda `POST /api/documentos`.
4. Back valida:
   - titulo
   - conteudo_final
   - cliente_id pertence ao usuario
   - template_id pertence ao usuario
5. Back salva documento com `user_id`.
6. Documento aparece somente para esse usuario.

---

## 31. Como o front e o back combinam nomes de campos

O back usa nomes em portugues:

```text
nome
cpf
telefone
titulo
conteudo
conteudo_final
cliente_id
template_id
```

O front as vezes usa nomes internos em ingles, mas antes de enviar para a API ele converte.

Exemplo conceitual:

```text
front: title
back: titulo

front: html
back: conteudo ou conteudo_final

front: clientId
back: cliente_id
```

Essa conversao fica no front, nos arquivos:

```text
src/lib/clients.ts
src/lib/templates.ts
src/lib/documents.ts
src/lib/api.ts
```

---

## 32. Seguranca atual

O que ja existe:

- Senha com hash.
- Token Sanctum.
- Rotas de dados protegidas por `auth:sanctum`.
- Filtro por `user_id`.
- Validacao de dados.
- Bloqueio para documento usar cliente/template de outro usuario.

O que pode melhorar depois:

- Tornar `user_id` obrigatorio no banco.
- Organizar migrations.
- Adicionar testes automatizados.
- Criar policies do Laravel.
- Restringir CORS em producao.
- Expirar tokens depois de determinado tempo.

---

## 33. Policies: melhoria futura interessante

Hoje a protecao esta dentro dos controllers:

```php
Cliente::where('user_id', $request->user()->id)->find($id);
```

Isso funciona.

Mas Laravel tambem tem um recurso chamado **Policy**.

Uma policy permite centralizar autorizacao:

```php
public function view(User $user, Cliente $cliente)
{
    return $cliente->user_id === $user->id;
}
```

Depois o controller poderia chamar:

```php
$this->authorize('view', $cliente);
```

Nao e obrigatorio agora, mas e uma evolucao boa quando o sistema crescer.

---

## 34. Testes que foram feitos

Foi testado:

- `GET /api/clientes` sem token retorna `401`.
- Usuario A cria cliente.
- Usuario B nao ve cliente do usuario A.
- Usuario B tentando acessar cliente do usuario A por ID recebe `404`.
- Usuario A cria documento.
- Usuario B nao ve documento do usuario A.
- Usuario B tentando criar documento com cliente/template do usuario A recebe `422`.

Esses testes confirmam que o isolamento por usuario esta funcionando.

---

## 35. Exercicios para estudar

### Exercicio 1: entender uma rota

Abra:

```text
routes/api.php
```

Escolha:

```php
Route::get('/clientes', [ClienteController::class, 'index']);
```

Depois abra:

```text
app/Http/Controllers/ClienteController.php
```

Ache o metodo:

```php
public function index(Request $request)
```

Leia linha por linha.

Perguntas:

- Como ele sabe qual usuario esta logado?
- Por que usa `where('user_id', ...)`?
- O que `latest()` faz?
- O que `response()->json(...)` devolve?

### Exercicio 2: criar cliente pelo PowerShell

1. Registre um usuario.
2. Guarde o token.
3. Crie um cliente.
4. Liste clientes.
5. Crie outro usuario.
6. Liste clientes com o outro usuario.

Voce deve ver que o segundo usuario nao enxerga o cliente do primeiro.

### Exercicio 3: tentar quebrar isolamento

Com o usuario B, tente acessar:

```text
GET /api/clientes/ID_DO_CLIENTE_DO_USUARIO_A
```

Esperado:

```text
404
```

### Exercicio 4: estudar o banco

No MySQL/phpMyAdmin, abra:

```text
users
clientes
templates
documentos
personal_access_tokens
```

Observe:

- Qual `id` do usuario?
- Qual `user_id` do cliente?
- Qual `user_id` do template?
- Qual `user_id` do documento?
- Qual `cliente_id` e `template_id` do documento?

---

## 36. Glossario rapido

### API

Interface que o front usa para conversar com o back.

### Endpoint

Uma URL especifica da API, por exemplo:

```text
/api/clientes
```

### Controller

Classe que recebe uma requisicao e devolve uma resposta.

### Model

Classe que representa uma tabela.

### Migration

Arquivo que cria ou altera tabelas.

### Token

Texto usado para provar que o usuario esta autenticado.

### Bearer token

Formato de envio do token:

```http
Authorization: Bearer TOKEN
```

### Middleware

Camada que roda antes do controller.

Exemplo:

```php
auth:sanctum
```

Esse middleware verifica autenticacao.

### Eloquent

ORM do Laravel para consultar banco usando PHP.

### Foreign key

Chave estrangeira. Liga uma tabela a outra.

Exemplo:

```text
documentos.cliente_id -> clientes.id
```

---

## 37. Ordem recomendada para estudar este back-end

1. Leia `routes/api.php`.
2. Leia `AuthController.php`.
3. Leia `User.php`.
4. Leia `ClienteController.php` junto com `Cliente.php`.
5. Leia `TemplateController.php` junto com `Template.php`.
6. Leia `DocumentoController.php` junto com `Documento.php`.
7. Abra o banco no phpMyAdmin e veja as tabelas.
8. Teste endpoints com PowerShell.
9. Volte ao front e veja como ele chama a API.

---

## 38. Resumo mental do sistema

Pense assim:

```text
Usuario faz login
Back entrega token
Front guarda token
Front manda token em cada chamada
Back identifica usuario pelo token
Back filtra tudo por user_id
Back salva novos registros com user_id
Usuario so ve seus proprios clientes/templates/documentos
```

Essa e a ideia central do back-end.

Se voce entender esse fluxo, voce ja entendeu a parte mais importante do sistema.

