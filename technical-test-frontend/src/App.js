import React, { useState } from 'react';
import { Container, Button } from 'react-bootstrap';
import SignUpModal from './components/SignUpModal';
import UserList from './components/UserList';
import 'bootstrap/dist/css/bootstrap.min.css';
import './App.css';

function App() {
  const [showModal, setShowModal] = useState(false);
  const [userListKey, setUserListKey] = useState(0);

  const handleClose = () => setShowModal(false);
  const handleShow = () => setShowModal(true);

  const handleUserRegistered = () => {
    // Trigger a re-render of UserList
    setUserListKey(prev => prev + 1);
  };

  return (
    <Container className="app-container">
      <h1 className="text-center my-4">User Management System</h1>
      
      <Button 
        variant="primary" 
        onClick={handleShow}
        className="mb-3"
      >
        Add New User
      </Button>

      <SignUpModal 
        show={showModal} 
        handleClose={handleClose}
        onUserRegistered={handleUserRegistered}
      />

      <UserList key={userListKey} />
    </Container>
  );
}

export default App;