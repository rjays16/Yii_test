import React, { useState } from 'react';
import axios from 'axios';

function SignUp() {
  const [name, setName] = useState('');
  const [birthday, setBirthday] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    // Basic validation
    if (!name || !birthday) {
      setError('Please fill in all fields');
      return;
    }

    try {
      const response = await axios.post('http://localhost:8080/api/signup', {
        name,
        birthday
      }, {
        headers: {
          'Content-Type': 'application/json'
        }
      });

      if (response.data.success) {
        setSuccess('User registered successfully');
        setName('');
        setBirthday('');
      } else {
        setError(response.data.message || 'Registration failed');
      }
    } catch (err) {
      setError('An error occurred. Please try again.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2>User SignUp</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Name:</label>
          <input 
            type="text" 
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Birthday:</label>
          <input 
            type="date" 
            value={birthday}
            onChange={(e) => setBirthday(e.target.value)}
            required
          />
        </div>
        <button type="submit">Register</button>
      </form>
      {error && <p style={{color: 'red'}}>{error}</p>}
      {success && <p style={{color: 'green'}}>{success}</p>}
    </div>
  );
}

export default SignUp;