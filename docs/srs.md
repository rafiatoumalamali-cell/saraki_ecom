# SOFTWARE REQUIREMENTS SPECIFICATION (SRS)

**Project Name:** SARAKI Digital Platform  
**Prepared by:** SahelSoft  
**Version:** 1.0  
**Date:** July 2026

---

## Document Control

| Item | Details |
|---|---|
| Document Title | Software Requirements Specification |
| Project | SARAKI Digital Platform |
| Client | SARAKI |
| Prepared by | SahelSoft |
| Version | 1.0 |
| Status | Draft |

---

## Table of Contents

1. Introduction
2. Overall Description
3. External Interface Requirements
4. System Features (Functional Requirements)
5. Non-functional Requirements
6. Database Requirements
7. Business Rules
8. Assumptions & Dependencies
9. Future Enhancements
10. Appendix

---

## 1. Introduction

### 1.1 Purpose

This Software Requirements Specification (SRS) defines the functional and non-functional requirements for the SARAKI Digital Platform.

The platform will enable SARAKI to establish a premium online presence, showcase its fashion collections, sell products online, manage appointments, communicate with customers, and administer website content through a secure dashboard.

This document serves as the technical reference for developers, testers, project managers, and stakeholders throughout the software development lifecycle.

### 1.2 Intended Audience

This document is intended for:

- Client (SARAKI)
- Project Manager
- Business Analyst
- Software Developers
- UI/UX Designers
- Database Designers
- Test Engineers
- Future Maintenance Team

### 1.3 Project Scope

The system will provide:

- Corporate website
- Online product catalogue
- E-commerce functionality
- Appointment booking
- Customer registration
- Secure administrator dashboard
- Content management
- Multilingual support
- WhatsApp integration
- Google Maps integration

---

## 2. Overall Description

### 2.1 Product Perspective

The SARAKI platform is a web-based Fashion Management and E-commerce System.

The platform consists of:

- Customer Website
- Admin Dashboard
- Database
- Payment Gateway
- Notification System

### 2.2 Product Functions

The system shall allow users to:

- Browse products
- Search collections
- Register/Login
- Place orders
- Book appointments
- Contact SARAKI
- Read blog posts
- View fashion gallery
- Track orders
- Change account information

Administrators shall be able to:

- Manage products
- Manage customers
- Manage appointments
- Process orders
- Manage website content
- Publish blogs
- Generate reports

### 2.3 User Classes

**Guest Visitor**

Can:

- Browse website
- View products
- View gallery
- Read blog
- Contact company

Cannot:

- Purchase
- Book appointment
- Track orders

**Registered Customer**

Can:

- Login
- Purchase products
- Manage profile
- Book appointments
- Track orders
- Save wishlist

**Administrator**

Has complete control over:

- Products
- Orders
- Customers
- Appointments
- Content
- Reports
- System Settings

### 2.4 Operating Environment

The system shall operate on:

**Desktop Browsers**

- Chrome
- Firefox
- Safari
- Edge

**Mobile Browsers**

- Android
- iPhone

**Server**

- PHP 8+
- MySQL

### 2.5 Constraints

The system must:

- Be responsive
- Support poor internet connections
- Follow MVC architecture
- Be secure
- Support English and French

---

## 3. External Interface Requirements

### 3.1 User Interface

The interface shall use:

**Primary Colors**

- Charcoal Black (#0A0A0A)
- Metallic Gold (#D4AF37)
- Beige (#F5F1E8)

**Typography**

- Headings: Playfair Display
- Body: Montserrat

**Style**

- Luxury
- Minimalist
- Elegant

### 3.2 Hardware Interface

Users only require:

- Smartphone
- Tablet
- Laptop/Desktop

Administrator requires:

- Computer
- Internet connection

### 3.3 Software Interface

The system integrates with:

- WhatsApp
- Google Maps
- Email Server
- Payment Gateway (future)
- Social Media Links

---

## 4. System Features

### Feature 1 — User Registration

**Description:** Customers can create accounts.

**Inputs:**

- Name
- Email
- Phone
- Password

**Outputs:** Account Created

**Priority:** High

### Feature 2 — User Login

The system shall authenticate users.

**Inputs:** Email, Password

**Outputs:** Dashboard

**Priority:** High

### Feature 3 — Product Catalogue

The system shall display:

- Categories
- Products
- Price
- Images
- Description

**Priority:** High

### Feature 4 — Shopping Cart

Users shall:

- Add products
- Remove products
- Update quantity

**Priority:** High

### Feature 5 — Checkout

Users shall provide:

- Delivery Information
- Contact Information

**Future:** Online Payment

**Priority:** High

### Feature 6 — Appointment Booking

Customers shall book:

- Consultation
- Measurement
- Fitting

System shall:

- Save booking
- Notify admin

**Priority:** High

### Feature 7 — Portfolio Gallery

Display:

- Collections
- Fashion Events
- Completed Designs

**Priority:** Medium

### Feature 8 — Blog Module

Admin shall:

- Publish articles
- Update articles
- Delete articles

**Priority:** Medium

### Feature 9 — Contact Module

Customers can:

- Send inquiries

System sends:

- Email notification

**Priority:** High

### Feature 10 — Admin Dashboard

Administrator shall manage:

- Products
- Customers
- Orders
- Appointments
- Blog
- Gallery
- Website Pages

**Priority:** Critical

---

## 5. Non-functional Requirements

### Performance

- Website loads within 3 seconds under normal conditions.
- Support at least 200 concurrent users.
- Product search should return results in under 2 seconds.

### Availability

- 99% uptime target.
- Daily automated backups.

### Security

The system shall:

- Encrypt passwords
- Use HTTPS
- Prevent SQL Injection
- Prevent XSS attacks
- Validate all user input
- Implement role-based access control

### Reliability

The platform shall recover gracefully after failures.

### Maintainability

- Modular MVC architecture
- Well-documented code
- Reusable components

### Scalability

The architecture shall support:

- More products
- More customers
- Future mobile app
- Future payment integrations

### Usability

The website shall:

- Be mobile responsive
- Be easy to navigate
- Use consistent design
- Support accessibility best practices

---

## 6. Database Requirements

Major entities include:

- Users
- Customers
- Products
- Categories
- Orders
- Order Items
- Appointments
- Gallery
- Blog Posts
- Contact Messages
- Reviews

A detailed Entity Relationship Diagram (ERD) and Data Dictionary will be provided in separate documents.

---

## 7. Business Rules

- Only administrators can manage website content.
- Customers must register before placing an order.
- Appointment slots cannot overlap.
- Products cannot have negative stock quantities.
- Orders cannot be edited after completion.
- Every blog post must have an author and publication date.

---

## 8. Assumptions and Dependencies

### Assumptions

- The client will provide all branding assets, product images, and content.
- Reliable hosting and domain services will be available before deployment.
- The client will review and approve deliverables promptly.

### Dependencies

- Domain registration.
- Web hosting environment.
- Payment gateway availability (future phase).
- Stable internet connection for deployment and maintenance.

---

## 9. Future Enhancements

Planned future versions may include:

- Mobile application (Android/iOS)
- AI-powered fashion recommendations
- AI virtual fitting assistant
- Loyalty and rewards program
- Customer reviews and ratings
- Inventory and production management
- SMS notifications
- Online payment integration
- Shipment tracking
- Analytics dashboard
- Multi-vendor marketplace support

---

## 10. Appendix

### Acronyms

| Acronym | Meaning |
|---|---|
| SRS | Software Requirements Specification |
| BRD | Business Requirements Document |
| MVC | Model-View-Controller |
| UI | User Interface |
| UX | User Experience |
| HTTPS | HyperText Transfer Protocol Secure |
| SQL | Structured Query Language |

### Document Approval

| Role | Name | Signature | Date |
|---|---|---|---|
| Client Representative | ______________ | ______________ | ______________ |
| Project Manager | Rafiatou Malam Ali | ______________ | ______________ |
| Technical Lead | Fannareme Aboubacar Abdou | ______________ | ______________ |
