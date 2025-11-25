-- Create remember_tokens table for secure "Remember Me" functionality
CREATE TABLE remember_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    user_type VARCHAR(10) NOT NULL, -- 'admin' or 'customer'
    token_hash VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster lookups
CREATE INDEX idx_remember_tokens_token ON remember_tokens(token_hash);
CREATE INDEX idx_remember_tokens_user ON remember_tokens(user_id, user_type);
CREATE INDEX idx_remember_tokens_expires ON remember_tokens(expires_at);
