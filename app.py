import subprocess
import json
import mysql.connector
from datetime import datetime
from dotenv import load_dotenv
import os
import pytz

load_dotenv()

def run_speedtest():
    result = subprocess.run(['speedtest', '-f', 'json'], capture_output=True, text=True)
    data = json.loads(result.stdout)
    
    utc_timestamp = datetime.strptime(data['timestamp'], "%Y-%m-%dT%H:%M:%SZ")
    utc_zone = pytz.utc
    brasil_zone = pytz.timezone('America/Sao_Paulo')
    timestamp_brasilia = utc_zone.localize(utc_timestamp).astimezone(brasil_zone)
    
    return {
        'timestamp': timestamp_brasilia.strftime("%Y-%m-%d %H:%M:%S"),
        'ping_latency': data['ping']['latency'],
        'download_bandwidth': data['download']['bandwidth'],
        'upload_bandwidth': data['upload']['bandwidth'],
        'packet_loss': data['packetLoss'],
        'server_name': data['server']['name'],
        'location': data['server']['location'],
        'country': data['server']['country']
    }

def save_to_db(data):
    conn = mysql.connector.connect(
        host=os.getenv("DB_HOST"),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASSWORD"),
        database=os.getenv("DB_NAME")
    )
    cursor = conn.cursor()

    sql = '''INSERT INTO test_results (timestamp, ping_latency, download_bandwidth,
               upload_bandwidth, packet_loss, server_name, location, country)
             VALUES (%s, %s, %s, %s, %s, %s, %s, %s)'''

    values = (data['timestamp'], data['ping_latency'], data['download_bandwidth'],
              data['upload_bandwidth'], data['packet_loss'], data['server_name'],
              data['location'], data['country'])

    cursor.execute(sql, values)
    conn.commit()
    cursor.close()
    conn.close()

if __name__ == "__main__":
    data = run_speedtest()
    save_to_db(data)