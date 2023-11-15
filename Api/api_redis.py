# Importa las bibliotecas necesarias
import os
import json
import redis
from flask import Flask, jsonify, request
from flask_cors import CORS
import pandas as pd

app = Flask(__name__)
CORS(app)  # Inicializa CORS

# Configuración de Redis desde variables de entorno
redis_host = os.getenv("REDIS_HOST", "localhost")
redis_port = int(os.getenv("REDIS_PORT", 6379))  # Asegúrate de convertir el puerto a entero
redis_conn = redis.StrictRedis(host=redis_host, port=redis_port, db=0, decode_responses=True)

ratings_path = os.getenv("RATINGS_PATH", './MovieLens-100K_Recommender-System/data/ratings.csv')
ratings = pd.read_csv(ratings_path)

@app.route("/api/ratings")
def get_ratings():
    redis_key = 'ratings_data'
    try:
        json_data = redis_conn.get(redis_key)
        if not json_data:
            json_data = ratings.head().to_json(orient="records")
            redis_conn.set(redis_key, json_data)
        return jsonify(json.loads(json_data))
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    
@app.route("/api/ratings/distribution")
def get_ratings_distribution():
    redis_key = 'ratings_distribution'
    try:
        json_data = redis_conn.get(redis_key)
        if not json_data:
            distribution_data = ratings['rating'].value_counts().sort_index(ascending=False)
            json_data = distribution_data.to_json()
            redis_conn.set(redis_key, json_data)
        return jsonify(json.loads(json_data))
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    
@app.route("/api/top-users")
def get_top_users():
    redis_key = 'top_users_data'
    try:
        top_users_data = redis_conn.get(redis_key)
        if not top_users_data:
            # Obtener los top users
            top_users_data = ratings.groupby('userId')['rating'].count().reset_index().sort_values('rating', ascending=False)[:10].to_json(orient='records')
            redis_conn.set(redis_key, top_users_data)
        return jsonify(json.loads(top_users_data))
    except Exception as e:
        return jsonify({"error": str(e)}), 500

# Configuración de puerto dinámica
port = os.getenv("API_PORT", 5000)
app.run(host="0.0.0.0", port=port)
