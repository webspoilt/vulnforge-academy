import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { Toaster } from "@/components/ui/toaster";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "VulnForge Academy | Security Training Platform",
  description: "Master ethical hacking with 20 deliberately vulnerable levels covering OWASP Top 10, SQL Injection, XSS, IDOR, SSRF, RCE and more. Capture flags, learn exploitation techniques.",
  keywords: ["VulnForge", "Cybersecurity", "Security Training", "CTF", "Hacking", "OWASP", "Penetration Testing", "Ethical Hacking"],
  authors: [{ name: "webspoilt" }],
  icons: {
    icon: "/favicon.ico",
  },
  openGraph: {
    title: "VulnForge Academy - Security Training Platform",
    description: "Master ethical hacking with 20 deliberately vulnerable levels",
    url: "https://github.com/webspoilt/vulnforge-academy",
    siteName: "VulnForge Academy",
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "VulnForge Academy",
    description: "Master ethical hacking with 20 deliberately vulnerable levels",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased`}
        style={{ backgroundColor: '#0a0a0f', color: '#e0e0e0' }}
      >
        {children}
        <Toaster />
      </body>
    </html>
  );
}
