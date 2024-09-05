from flask import Flask, render_template, request
from flask_sqlalchemy import SQLAlchemy
import pickle
import pandas as pd
import numpy as np
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import OrdinalEncoder, OneHotEncoder, MinMaxScaler

app = Flask(__name__)

# Configure the database URI
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://root:root@localhost/ehr'
db = SQLAlchemy(app)

# Load the trained model
rf = pickle.load(open('model.pkl', 'rb'))

# Define the preprocessor used during model training
oe_order = ['never', 'former', 'not current', 'current', 'ever']

# The preprocessor should be the same as used during training
preprocessor = ColumnTransformer(transformers=[
    ('oe', OrdinalEncoder(categories=[oe_order]), ['smoking_history']),
    ('ohe', OneHotEncoder(drop='first'), ['gender']),
    ('scaler', MinMaxScaler(), ['age', 'bmi', 'blood_glucose_level', 'HbA1c_level'])
], remainder='passthrough')

# Function to handle missing values like in the training script
def SmokingHistoryImpute(data, column):
    mask = data[column].isnull()
    num_missing = mask.sum()
    if num_missing > 0:
        random_sample = data[column].dropna().sample(num_missing, replace=True)
        data.loc[mask, column] = random_sample.values

@app.route('/')
def home():
    return render_template('Insights.html')

@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Fetch data from the database
        query = "SELECT Gender, Age, Hypertension, HeartDisease, SmokingHistory, BMI, HbA1cLevel, BloodGlucoseLevel FROM MedicalHistory"
        data = pd.read_sql(query, db.engine)

        # Handle missing values (as done during training)
        SmokingHistoryImpute(data, 'SmokingHistory')

        # Make sure the column names match the training data
        data.columns = ['gender', 'age', 'hypertension', 'heart_disease', 'smoking_history', 'bmi', 'HbA1c_level', 'blood_glucose_level']

        # Preprocess the data (use the preprocessor from training)
        X_trf = preprocessor.transform(data)

        # Predict using the model
        result = rf.predict(X_trf)

        # Pass predictions to the template
        return render_template('Insights.html', predictions=result.tolist())
    except Exception as e:
        return str(e)  # Return error message in case of failure

if __name__ == '__main__':
    app.run(debug=True)
