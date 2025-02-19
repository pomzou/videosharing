# System Patterns

## Architecture Overview

### Application Structure
- Laravel MVC Framework
- Blade Template Engine
- AWS S3 Integration
- Docker Containerization

## Core Components

### Models
1. Video Management
   - VideoFile: Handles video file metadata and storage
   - VideoShare: Manages sharing permissions and access
   - AccessLog: Tracks video access and usage

2. User System
   - User: Manages authentication and profile data
   - Profile: Handles user preferences and settings

### Controllers
1. Video Controllers
   - VideoFileController: Handles upload and storage
   - VideoShareController: Manages sharing and access
   - VideoController: General video management

2. Authentication
   - Standard Laravel authentication
   - Profile management

## Design Patterns

### Repository Pattern
- Separation of data access logic
- Clean interface for model interactions
- Consistent data handling

### Service Layer
- Business logic encapsulation
- AWS S3 integration
- Video processing services

### Event System
- Video upload events
- Share creation events
- Access logging events

## Data Flow

### Video Upload
1. File validation
2. S3 upload process
3. Metadata storage
4. Event dispatching

### Video Sharing
1. Access token generation
2. Expiration handling
3. Email notifications
4. Access logging

## Security Patterns

### Access Control
- Authentication middleware
- Share token validation
- Expiration enforcement
- Privacy settings checks

### Storage Security
- S3 bucket policies
- Signed URLs
- Temporary credentials
- Access logging

## Infrastructure

### Docker Environment
- Application container
- Database container
- Development tooling

### AWS Integration
- S3 for video storage
- IAM roles and policies
- Lambda functions
- Terraform infrastructure

## Testing Strategy
1. Unit Tests
   - Model validation
   - Service logic
   - Controller actions

2. Feature Tests
   - Upload workflow
   - Sharing process
   - Access control

3. Integration Tests
   - S3 integration
   - Email sending
   - URL signing
