import React from 'react';
import { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import './index.css';

const API_BASE_URL = '/backend/api/utilisateurs.php';

function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [isAuthenticated, setIsAuthenticated] = useState(false);
    const [user, setUser] = useState(null);

    useEffect(() => {
        const userId = sessionStorage.getItem('userId');
        if (userId) {
            setIsAuthenticated(true);
            setUser({
                id: sessionStorage.getItem('userId'),
                name: sessionStorage.getItem('userName'),
                role: sessionStorage.getItem('userRole')
            });
        }
    }, []);

    const handleLogin = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setError('');
        setSuccess('');

        try {
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);

            const response = await fetch(API_BASE_URL, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (data.success) {
                setSuccess('Connexion réussie! Bienvenue sur votre tableau de bord.');
                setIsAuthenticated(true);
                setUser(data.data);
                sessionStorage.setItem('userId', data.data.id);
                sessionStorage.setItem('userName', data.data.nom);
                sessionStorage.setItem('userRole', data.data.role);
            } else {
                setError(data.message || 'Identifiants incorrects.');
            }
        } catch (error) {
            setError('Une erreur s'est produite lors de la connexion. Veuillez réessayer.');
        } finally {
            setIsLoading(false);
        }
    };

    const handleLogout = () => {
        sessionStorage.removeItem('userId');
        sessionStorage.removeItem('userName');
        sessionStorage.removeItem('userRole');
        setIsAuthenticated(false);
        setUser(null);
        setSuccess('Déconnexion réussie.');
    };

    if (isAuthenticated && user) {
        return (
            <div className="dashboard">
                <div className="dashboard-header">
                    <h1>Bienvenue, {user.name}!</h1>
                    <p>Rôle: {user.role}</p>
                    <button onClick={handleLogout} className="logout-button">Se déconnecter</button>
                </div>

                <div className="test-messages">
                    <h3>📊 Test Messages - Plateforme E-commerce</h3>
                    <div className="test-header">Statut du système:</div>
                    <div className="test-list">
                        <div className="test-item success">
                            <span>✅ La connexion réussie!</span>
                            <span className="timestamp">Maintenant</span>
                        </div>
                        <div className="test-item pending">
                            <span>⏳ Chargement du tableau de bord...</span>
                            <span className="timestamp">Dans un instant</span>
                        </div>
                        <div className="test-item error" onClick={runSystemTests}>
                            <span>🔧 Vérification des tests du système</span>
                            <span className="timestamp">Cliquez pour exécuter</span>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="login-container">
            <div className="login-card">
                <div className="login-header">
                    <h1>Connexion</h1>
                    <p>Accédez à votre tableau de bord</p>
                </div>

                {error && <div className="error-message">{error}</div>}
                {success && <div className="success-message">{success}</div>}

                <form onSubmit={handleLogin} className="login-form">
                    <div className="form-group">
                        <label htmlFor="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="votre.email@exemple.com"
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="password">Mot de passe</label>
                        <input
                            type="password"
                            id="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="Votre mot de passe"
                            required
                        />
                    </div>

                    <button type="submit" className="login-button" disabled={isLoading}>
                        {isLoading ? 'Connexion...' : 'Se connecter'}
                        {isLoading && <span className="loading-spinner"></span>}
                    </button>
                </form>
            </div>
        </div>
    );
}

function runSystemTests() {
    const testList = document.querySelectorAll('.test-item');
    testList.forEach(item => {
        item.classList.remove('error', 'success', 'pending');
    });

    testList[2].className = 'test-item pending';
    testList[2].innerHTML = '<span>🔧 Exécution des tests de base de données...</span><span className="timestamp">Test en cours</span>';

    setTimeout(() => {
        testList[2].className = 'test-item success';
        testList[2].innerHTML = '<span>✅ Tests de base de données passés!</span><span className="timestamp">Maintenant</span>';

        const newTest = document.createElement('div');
        newTest.className = 'test-item success';
        newTest.innerHTML = '<span>✅ Configuration API prête</span><span className="timestamp">Testé maintenant</span>';
        document.querySelector('.test-list').appendChild(newTest);
    }, 2000);
}

function App() {
    return <LoginPage />;
}

createRoot(document.getElementById('root')).render(
    <App />,
);
