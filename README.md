
# SpeedTest Widget API pfSense

Esta é uma API que executa testes de velocidade da internet usando o `speedtest`, armazena os resultados em um banco de dados MySQL e mostra os dados mais recentes em um widget no pfSense.

## Dependências

As seguintes dependências são necessárias para o funcionamento da API:

- `Flask`: Framework web para Python.
- `mysql-connector-python`: Conector MySQL para Python.
- `python-dotenv`: Carrega variáveis de ambiente a partir de um arquivo `.env`.
- `pytz`: Módulo para manipulação de fusos horários.


## Instalação do speedtest-cli
Para realizar os testes de velocidade, você precisa instalar o `speedtest`. 

Informações de instalação estão disponíveis em: [Speedtest CLI Installation](https://www.speedtest.net/pt/apps/cli).

## Criando o banco de dados

1. **Acesse o MariaDB**:

   Abra o terminal e execute o seguinte comando para acessar o MariaDB:
   ```bash
   mysql -u root -p
   ```

2. **Criar o Banco de Dados:**
   ```sql
   CREATE DATABASE speedtest_db;
   USE speedtest_db;
   ```

3. **Criar a tabela `test_results`:**
   ```sql
   CREATE TABLE test_results (
       id INT AUTO_INCREMENT PRIMARY KEY,
       timestamp DATETIME NOT NULL,
       ping_latency FLOAT NOT NULL,
       download_bandwidth BIGINT NOT NULL,
       upload_bandwidth BIGINT NOT NULL,
       packet_loss FLOAT NOT NULL,
       server_name VARCHAR(255) NOT NULL,
       location VARCHAR(255) NOT NULL,
       country VARCHAR(255) NOT NULL
   );
   ```

4. **Criar o usuário de serviço e permitir acesso:**
   ```sql
   CREATE USER 'seu_usuario'@'localhost' IDENTIFIED BY 'suasenha';
   GRANT ALL PRIVILEGES ON speedtest_db.* TO 'seu_usuario'@'localhost';
   ```

## Configuração do Ambiente

1. **Instale as dependências usando PIP:**
   ```bash
   pip install -r requirements.txt
   ```

2. **Edite o arquivo `.env`:**
   - Renomeie o `.env.example` para `.env` e ajuste as informações necessárias.

## Automação do Teste de Velocidade

1. **Crie um cron para rodar o teste a cada 10 minutos:**
   - Abra o crontab:
   ```bash
   crontab -e
   ```

   - Insira a linha ao final do arquivo:
   ```
   */10 * * * * /usr/bin/python3 /caminhodoclone/app.py
   ```

   - Salve e saia do arquivo.

2. **Crie um serviço para a API:**
   - Crie um arquivo chamado `apidataspeedtest.service` no diretório `/etc/systemd/system/`:
   ```bash
   sudo nano /etc/systemd/system/apidataspeedtest.service
   ```

   - Adicione o seguinte conteúdo ao arquivo:
   ```ini
   [Unit]
   Description=API dados SpeedTest
   After=network.target

   [Service]
   ExecStart=/usr/bin/python3 /caminhodoclone/api.py
   WorkingDirectory=/caminhodoclone/
   StandardOutput=journal
   StandardError=journal
   Restart=always

   [Install]
   WantedBy=multi-user.target
   ```

   - Certifique-se de substituir `/caminhodoclone/api.py` pelo caminho real do seu arquivo `api.py`.

3. **Habilite e inicie o serviço:**
   ```bash
   sudo systemctl enable apidataspeedtest.service
   sudo systemctl start apidataspeedtest.service
   ```

4. **Verifique o status do serviço:**
   ```bash
   sudo systemctl status apidataspeedtest.service
   ```
   
