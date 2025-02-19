import React, { useState } from 'react';
import { Modal, Button, Form, Alert } from 'react-bootstrap';
import axios from 'axios';
import * as Yup from 'yup';

// Validation schema
const signupSchema = Yup.object().shape({
  name: Yup.string()
    .matches(/^[a-zA-Z\s-']+$/, 'Name can only contain letters, spaces, hyphens, and apostrophes')
    .required('Name is required'),
  birthday: Yup.date()
    .max(new Date(), 'Birthday cannot be in the future')
    .required('Birthday is required')
});

function SignUpModal({ show, handleClose, onUserRegistered }) {
  const [formData, setFormData] = useState({
    name: '',
    birthday: ''
  });
  const [errors, setErrors] = useState({});
  const [backendError, setBackendError] = useState('');

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setBackendError('');

    try {
      // Validate form data
      await signupSchema.validate(formData, { abortEarly: false });

      // Submit to backend
      const response = await axios.post('http://localhost:8080/api/signup', formData, {
        headers: {
          'Content-Type': 'application/json'
        }
      });

      if (response.data.success) {
        // Reset form and close modal
        setFormData({ name: '', birthday: '' });
        onUserRegistered();
        handleClose();
      } else {
        // Handle backend validation errors
        setBackendError(response.data.message || 'Registration failed');
      }
    } catch (err) {
      // Handle Yup validation errors
      if (err.inner) {
        const validationErrors = {};
        err.inner.forEach(error => {
          validationErrors[error.path] = error.message;
        });
        setErrors(validationErrors);
      } else if (err.response) {
        // Handle axios error
        setBackendError(err.response.data.message || 'An error occurred');
      } else {
        setBackendError('An unexpected error occurred');
      }
    }
  };

  return (
    <Modal show={show} onHide={handleClose}>
      <Modal.Header closeButton>
        <Modal.Title>User Registration</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <Form onSubmit={handleSubmit}>
          <Form.Group className="mb-3">
            <Form.Label>Name</Form.Label>
            <Form.Control 
              type="text" 
              name="name"
              value={formData.name}
              onChange={handleChange}
              isInvalid={!!errors.name}
            />
            <Form.Control.Feedback type="invalid">
              {errors.name}
            </Form.Control.Feedback>
          </Form.Group>

          <Form.Group className="mb-3">
            <Form.Label>Birthday</Form.Label>
            <Form.Control 
              type="date" 
              name="birthday"
              value={formData.birthday}
              onChange={handleChange}
              isInvalid={!!errors.birthday}
            />
            <Form.Control.Feedback type="invalid">
              {errors.birthday}
            </Form.Control.Feedback>
          </Form.Group>

          {backendError && (
            <Alert variant="danger">
              {backendError}
            </Alert>
          )}

          <Button variant="primary" type="submit">
            Register
          </Button>
        </Form>
      </Modal.Body>
    </Modal>
  );
}

export default SignUpModal;