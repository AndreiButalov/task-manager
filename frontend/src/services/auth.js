import api from './api'

const TOKEN_KEY = 'auth_token'
const USER_KEY = 'auth_user'

export function login(email, password) {
  return api.post('/login', { email, password }).then((response) => {
    const { token, user } = response.data

    localStorage.setItem(TOKEN_KEY, token)
    localStorage.setItem(USER_KEY, JSON.stringify(user))

    return user
  })
}

export function register(email, password, repeatPassword) {
  return api.post('/register', {
    email,
    password,
    repeat_password: repeatPassword,
  })
}

export function logout() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function getUser() {
  const raw = localStorage.getItem(USER_KEY)
  return raw ? JSON.parse(raw) : null
}

export function isAuthenticated() {
  return Boolean(getToken())
}
