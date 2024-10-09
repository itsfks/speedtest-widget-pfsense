from flask import Flask, jsonify
import mysql.connector
from dotenv import load_dotenv
import os
from datetime import datetime
import pytz

load_dotenv()

app = Flask(__name__)

def connect_db():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST"),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASSWORD"),
        database=os.getenv("DB_NAME")
    )

@app.route('/speedtest/latest', methods=['GET'])
def get_latest_speedtest():
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)    
    cursor.execute('''SELECT timestamp, ping_latency, download_bandwidth, 
                      upload_bandwidth, packet_loss, server_name, 
                      location, country 
                      FROM test_results 
                      ORDER BY timestamp DESC LIMIT 1''')

    result = cursor.fetchone()

    cursor.close()
    conn.close()

    if result:
        if isinstance(result['timestamp'], str):
            utc_zone = pytz.utc
            brasil_zone = pytz.timezone('America/Sao_Paulo')
            timestamp_gmt = datetime.strptime(result['timestamp'], "%a, %d %b %Y %H:%M:%S %Z")
            timestamp_brasilia = utc_zone.localize(timestamp_gmt).astimezone(brasil_zone)
        else:
            timestamp_brasilia = result['timestamp']

        return jsonify({
            'timestamp': timestamp_brasilia.strftime("%Y-%m-%d %H:%M:%S"),
            'ping_latency': result['ping_latency'],
            'download_bandwidth': result['download_bandwidth'],
            'upload_bandwidth': result['upload_bandwidth'],
            'packet_loss': result['packet_loss'],
            'server_name': result['server_name'],
            'location': result['location'],
            'country': result['country']
        }), 200
    else:
        return jsonify({'message': 'No speedtest data found'}), 404

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)