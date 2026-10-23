-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2020 at 01:57 AM
-- Server version: 10.1.31-MariaDB
-- PHP Version: 7.2.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `min`
--

-- --------------------------------------------------------

--
-- Table structure for table `car`
--

CREATE TABLE `car` (
  `Time(minutes)` int(11) NOT NULL DEFAULT '0',
  `Manufacturer` varchar(128) DEFAULT NULL,
  `Model` varchar(128) DEFAULT NULL,
  `Year` varchar(128) DEFAULT NULL,
  `Color` varchar(128) DEFAULT NULL,
  `Stocknumber` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`Time(minutes)`, `Manufacturer`, `Model`, `Year`, `Color`, `Stocknumber`) VALUES
(50, 'Ford', 'Galaxie', '1964', 'Black', '1'),
(89, 'Honda', 'Insight', '2007', 'Silver', '5'),
(100, 'Mercury', 'LeSabre', '2005', 'Brown', '20'),
(230, 'Chevrolet', 'Aveo', '2013', 'Blue', '14'),
(402, 'Datsun', '240Z', '1979', 'Orange', '3'),
(805, 'Porsche', 'Boxster', '2014', 'Green', '25');

-- --------------------------------------------------------

--
-- Table structure for table `car1`
--

CREATE TABLE `car1` (
  `Time(minutes)` int(11) NOT NULL,
  `Manufacturer` varchar(128) DEFAULT NULL,
  `Model` varchar(128) DEFAULT NULL,
  `Year` varchar(128) DEFAULT NULL,
  `Color` varchar(128) DEFAULT NULL,
  `Stocknumber` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `car2`
--

CREATE TABLE `car2` (
  `CarID` int(11) NOT NULL,
  `Time(minutes)` varchar(128) DEFAULT NULL,
  `Manufacturer` varchar(128) DEFAULT NULL,
  `Model` varchar(128) DEFAULT NULL,
  `Year` varchar(128) DEFAULT NULL,
  `Color` varchar(128) DEFAULT NULL,
  `Stocknumber` varchar(128) DEFAULT NULL,
  `ScheduledDate` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `car2`
--

INSERT INTO `car2` (`CarID`, `Time(minutes)`, `Manufacturer`, `Model`, `Year`, `Color`, `Stocknumber`, `ScheduledDate`) VALUES
(1, '50', 'Ford', 'Galaxie', '1964', 'Black', '1', NULL),
(2, '89', 'Honda', 'Insight', '2007', 'Silver', '5', NULL),
(3, '100', 'Mercury', 'LeSabre', '2005', 'Brown', '20', NULL),
(4, '230', 'Chevrolet', 'Aveo', '2013', 'Blue', '14', NULL),
(5, '402', 'Datsun', '240Z', '1979', 'Orange', '3', NULL),
(6, '805', 'Porsche', 'Boxster', '2014', 'Green', '25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `car3`
--

CREATE TABLE `car3` (
  `Car3ID` int(11) NOT NULL,
  `Time(minutes)` varchar(128) DEFAULT NULL,
  `Manufacturer` varchar(128) DEFAULT NULL,
  `Model` varchar(128) DEFAULT NULL,
  `Year` varchar(128) DEFAULT NULL,
  `Color` varchar(128) DEFAULT NULL,
  `Stocknumber` varchar(128) DEFAULT NULL,
  `ScheduledDate` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `car3`
--

INSERT INTO `car3` (`Car3ID`, `Time(minutes)`, `Manufacturer`, `Model`, `Year`, `Color`, `Stocknumber`, `ScheduledDate`) VALUES
(1, '50', 'Ford', 'Galaxie', '1964', 'Black', '1', NULL),
(2, '89', 'Honda', 'Insight', '2007', 'Silver', '5', NULL),
(3, '100', 'Mercury', 'LeSabre', '2005', 'Brown', '20', NULL),
(4, '230', 'Chevrolet', 'Aveo', '2013', 'Blue', '14', NULL),
(5, '402', 'Datsun', '240Z', '1979', 'Orange', '3', NULL),
(6, '805', 'Porsche', 'Boxster', '2014', 'Green', '25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `carlist`
--

CREATE TABLE `carlist` (
  `Time(minutes)` varchar(128) NOT NULL,
  `Manufacturer` varchar(128) DEFAULT NULL,
  `Model` varchar(128) DEFAULT NULL,
  `Year` varchar(128) DEFAULT NULL,
  `Color` varchar(128) DEFAULT NULL,
  `Stocknumber` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `carlist`
--

INSERT INTO `carlist` (`Time(minutes)`, `Manufacturer`, `Model`, `Year`, `Color`, `Stocknumber`) VALUES
('100', 'Mercury', 'LeSabre', '2005', 'Brown', '20'),
('230', 'Chevrolet', 'Aveo', '2013', 'Blue', '14'),
('402', 'Datsun', '240Z', '1979', 'Orange', '3'),
('50', 'Ford', 'Galaxie', '1964', 'Black', '1'),
('805', 'Porsche', 'Boxster', '2014', 'Green', '25'),
('89', 'Honda', 'Insight', '2007', 'Silver', '5');

-- --------------------------------------------------------

--
-- Table structure for table `cats`
--

CREATE TABLE `cats` (
  `id` smallint(6) NOT NULL,
  `family` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `age` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `classics`
--

CREATE TABLE `classics` (
  `author` varchar(128) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `category` varchar(16) DEFAULT NULL,
  `year` char(4) DEFAULT NULL,
  `isbn` char(13) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `classics`
--

INSERT INTO `classics` (`author`, `title`, `category`, `year`, `isbn`) VALUES
('Mark Twain', 'The Adventures of Tom Sawyer', 'Fiction', '1876', '9781598184891'),
('Jane Austen', 'Pride and Prejudice', 'Fiction', '1811', '9780582506206'),
('Charles Darwin', 'The Origin of Species', 'Non-Fiction', '1856', '9780517123201'),
('Charles Dickens', 'The Old Curiosity Shop', 'Fiction', '1841', '9780099533474'),
('William Shakespeare', 'Romeo and Juliet', 'Play', '1594', '9780192814968'),
('Herman Melville', 'Moby Dick', 'Fiction', '1851', '9780199535729');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `number`, `prefix`, `name`, `description`, `created_at`, `updated_at`) VALUES
(73, '5301', 'CS', 'Professional and Technical Communication', 'This course utilizes an integrated approach to writing and speaking for the technical professions. The advanced writing components of the course focus on writing professional quality technical documents such as proposals, memos, abstracts, reports, letters, emails, etc. The advanced oral communication components of the course focus on planning, developing, and delivering dynamic, informative and persuasive presentations. Advanced skills in effective teamwork, leadership, listening, multimedia and computer generated visual aids are also emphasized. Graduate students will have a successful communication experience working in a functional team environment using a real time, online learning environment. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(74, '5303', 'CS', 'Computer Science I', 'Computer science problem solving. The structure and nature of algorithms and their corresponding computer program implementation. Programming in a high level block-structured language (e.g., PASCAL, Ada, C++, or JAVA). Elementary data structures: arrays, records, linked lists, trees, stacks and queues. Prerequisite: ENCS majors only. (3-0) R', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(75, '5330', 'CS', 'Computer Science II', 'Basic concepts of computer organization: Numbering systems, two\'s complement notation, multi-level machine concepts, machine language, assembly programming and optimization, subroutine calls, addressing modes, code generation process, CPU datapath, pipelining, RISC, CISC, and performance calculation. Prerequisite or Corequisite: CS 5303. (3-0) R', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(76, '5333', 'CS', 'Discrete Structures', 'Mathematical foundations of computer science. Logic, sets, relations, graphs and algebraic structures. Combinatorics and metrics for performance evaluation of algorithms. Prerequisite: ENCS majors only. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(77, '5336', 'CS', 'Programming Projects in Java', 'Overview of the object-oriented philosophy. Implementation of object-oriented designs using the Java programming environment. Emphasis on using the browser to access and extend the Java class library. Prerequisite: CS 5303 or equivalent experience. (3-0) R', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(78, '5343', 'CS', 'Algorithm Analysis and Data Structures', 'Formal specifications and representation of lists, arrays, trees, graphs, multilinked structures, strings, and recursive pattern structures. Analysis of associated algorithms. Sorting and searching, file structures. Relational data models. Prerequisite: CS 5303. Prerequisite or Corequisite: CS 5333. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(79, '5348', 'CS', 'Operating Systems Concepts', 'Processes and threads. Concurrency issues including semaphores, monitors and deadlocks. Simple memory management. Virtual memory management. CPU scheduling algorithms. I/O management. File management. Introduction to distributed systems. Must have a working knowledge of C and Unix. Prerequisite: CS 5330. Prerequisite or Corequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(80, '5349', 'CS', 'Automata Theory', 'Deterministic and nondeterministic finite automata; regular expressions, regular sets, context-free grammars, pushdown automata, context free languages. Selected topics from Turing Machines and undecidability. Prerequisite: CS 5333. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(81, '5375', 'CS', 'Principles of UNIX', 'Design and history of the UNIX operating system. Detailed study of process and file system data structures. Shell programming in UNIX. Use of process-forking functionality of UNIX to simplify complex problems. Interprocess communication and coordination. Device drivers and streams as interfaces to hardware features. TCP/IP and other UNIX inter-machine communication facilities. Recommended prerequisite: CS 3335. Prerequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(82, '5390', 'CS', 'Computer Networks', 'The design and analysis of protocols for computer networking. Topics include: network protocol design and composition via layering, contention resolution in multi-access networks, routing metrics and optimal path searching, traffic management, global network protocols; dealing with heterogeneity and scalability. Prerequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(83, '5V71', 'CS', 'Cooperative Education', 'Placement in a faculty-supervised work environment in industry or government. Sites may be local or out-of-state. The cooperative education program provides exposure to a professional working environment, application of theory to working realities, and an opportunity to test skills and clarify goals. Experience gained may also serve as a work credential after graduation. May be repeated for credit (9 semester credit hours maximum). Prerequisites: ENCS majors only and department consent required. ([1-3]-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(84, '5V81', 'CS', 'Special Topics in Computer Science', 'May be repeated as topics vary (9 semester credit hours maximum). Prerequisites: ENCS majors only and instructor consent required. ([1-9]-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(85, '6301', 'CS', 'Special Topics in Computer Science', 'May be repeated for credit as topics vary. Prerequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(86, '6304', 'CS', 'Computer Architecture', 'Trends in processor, memory, I/O and system design. Techniques for quantitative analysis and evaluation of computer systems to understand and compare alternative design choices in system design. Components in high performance processors and computers: pipelining, instruction level parallelism, memory hierarchies, and input/output. Students will undertake a major computing system analysis and design project. Must have an understanding of C/C++. Prerequisite: CS 3340 or EE 4304. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(87, '6307', 'CS', 'Introduction to Big Data Management and Analytics for non CS-Majors', 'Database fundamentals including Query Processing, Parallel/Distributed Data Processing, NoSQL systems (e.g., Key-value stores, Column-oriented data stores, MapReduce), Graph Processing, and Recommendation Systems using Big Data technologies. This course cannot be used to satisfy the requirements for the MS CS or the MS SE degree plans. Prerequisites: (CS 5303 or equivalent) and (CS 5343 or equivalent). (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(88, '6313', 'CS', 'Statistical Methods for Data Science', 'Statistical methods for data science. Statistical Methods are developed at an intermediate level. Sampling distributions. Point and interval estimation. Parametric and nonparametric hypothesis testing. Analysis of variance. Regression, model building and model diagnostics. Monte Carlo simulation and bootstrap. Introduction to a statistical software package. Prerequisite: CS 3341 or SE 3341 or STAT 3341 or equivalent. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(89, '6314', 'CS', 'Web Programming Languages', 'Advanced understanding of web architecture, standards, protocols, tools, and technologies. Tools required for web programming including HTML, CSS, and JavaScript; XML and database technologies; server-side programming using PHP; Web security protocols and standards; techniques and algorithms related to web services, cloud computing, and semantic web. Prerequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(90, '6315', 'CS', 'Semantic Web', 'History and foundations of semantic web. URIs and namespaces; XML and XMLS Datatypes, RDF and RDF/XML, RDFS, and OWL (Lite, DL and Full); applications of semantic web; introduction to OWL 2 features and profiles; design patterns used in the creation of semantic web solutions. Prerequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(91, '6320', 'CS', 'Natural Language Processing', 'This course covers state-of-the-art methods for natural language processing. After an introduction to the basics of syntax, semantic, and discourse analysis, the focus shifts to the integration of these modules into natural-language processing systems. In addition to natural language understanding, the course presents advanced material on lexical knowledge acquisition, natural language generation, machine translation, and parallel processing of natural language. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(92, '6321', 'CS', 'Discourse Processing', 'Introduction to discourse processing from natural language texts. Automatic clustering of utterances into coherent units (segments) with hierarchical structures. State-of-the-art research in textual cohesion, coherence, and discourse understanding. Included topics are anaphoric reference and ellipsis, notion of textual context, and relationship between tense, aspect, and discourse states. Prerequisite: CS 6320. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(93, '6322', 'CS', 'Information Retrieval', 'This course covers modern techniques for storing and retrieving unformatted textual data and providing answers to natural language queries. Current research topics and applications of information retrieval in data mining, data warehousing, text mining, digital libraries, hypertext, multimedia data, and query processing are also presented. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(94, '6323', 'CS', 'Computer Animation and Gaming', 'Theoretical foundations and programming techniques involved in computer animation and game engines. Specific topics include 2D and 3D transformations, skeletons, forward and inverse kinematics, skinning, keyframing, particle systems, rigid bodies, cloth animation, collision detection, and animation for video games. Prerequisites: CS 5343 and a good working knowledge of C++ and graphical programming (either OpenGL, DirectX, or Java3D). (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(95, '6324', 'CS', 'Information Security', 'A comprehensive study of security vulnerabilities in information systems and the basic techniques for developing secure applications and practicing safe computing. Topics include common attacking techniques such as buffer overflow, Trojan, virus, etc. UNIX, Windows and Java security. Conventional encryption. Hashing functions and data integrity. Public-key encryption (RSA, Elliptic-Curve). Digital signature. Watermarking for multimedia. Security standards and applications. Building secure software and systems. Management and analysis of security. Legal and ethical issues in computer security. Prerequisites: CS 5343 and CS 5348. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(96, '6325', 'CS', 'Introduction to Bioinformatics', 'The course provides a broad overview of the bioinformatics field. Comprehensive introduction to molecular biology and molecular genetics for a program of study in bioinformatics. Discussion of elementary computer algorithms in biology (e.g., sequence alignment and gene finding). Biological databases, data analysis and management. Prerequisite: CS 5343. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(97, '6326', 'CS', 'Human Computer Interactions', 'In-depth exploration of human-computer interactions (HCI). Models and principles of HCI. The user experience (UX) lifecycle and design guidelines for a wide variety of advanced interfaces, such as mobile devices and 3D sensors. UX evaluation of interface designs. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(98, '6327', 'CS', 'Video Analytics', 'In-depth analysis of topics such as: video features for (human) activity and event detection; large-scale video event classification algorithms; objects-in-video counting approaches; multi-camera video handling; compressed video event detection and analyzing video in large-scale human traffic areas (such as shopping malls, airports, train-stations, etc.). Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(99, '6328', 'CS', 'Modeling and Simulation', 'Theory and practice of modeling, including models for concepts, knowledge, geometry, and dynamics. A variety of model types are covered along with their algebraic and diagrammatic representations. Creative media design and representation of models is stressed. Prerequisite: CS 5343 or instructor consent required. (3-0) R', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(100, '6331', 'CS', 'Multimedia Systems', 'Analyzing and understanding how different media data (such as text, audio, images, video, and 3D graphics) can be stored, queried, delivered, and consumed. These aspects will be discussed from the point of view of the operating system, databases, and networking. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(101, '6332', 'CS', 'Systems Security and Malicious Code Analysis', 'Concepts, techniques, and tools to capture the structure, format, and representation of binary code, and transform them for higher level analysis. Use of static analysis including data-flow analysis, point-to analysis, and shape analysis to reason about the abstractions inside binary code. Use of dynamic binary instrumentation to trace the instruction level behavior of both benign and malicious programs. Use of virtual machines to observe the whole system level behavior including OS kernels. Prerequisites: CS 5343 and CS 5348 and knowledge of Assembly Code. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(102, '6333', 'CS', 'Algorithms in Computational Biology', 'The principles of algorithm design for biological datasets, and analysis of influential problems and techniques. Biological sequence analysis, gene finding, RNA folding, protein folding, sequence alignment, genome assembly, comparative genomics, phylogenetics, clustering algorithms. Prerequisite: CS 6325. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(103, '6334', 'CS', 'Virtual Reality', 'Theory and practice of virtual reality (VR). Provides in-depth overview of VR, including input devices, output devices, 3D navigation techniques, 3D selection and manipulation techniques, system control techniques, interaction fidelity, scenario fidelity, display fidelity, design guidelines, and evaluation methods. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(104, '6343', 'CS', 'Cloud Computing', 'Different layers of cloud computing, infrastructure as a service (IaaS), platform as a service (PaaS), and software as a service (SaaS). Data centers. Resource management, power management, and health monitoring in IaaS cloud. Hadoop MapReduce for big data computing. PaaS examples such as GAE, Force.com. SaaS concepts and enabling technologies. Cloud storage theory and practical solutions such as GFS, Big Table, HDFS, HBase, Dynamo, Pnuts. Erasure coding and secret sharing based cloud storage. Virtualization and emulation. Server virtualization, storage virtualization, and network virtualization. Cloud security. Prerequisites: CS 5343 and CS 5348. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(105, '6347', 'CS', 'Statistical Methods in AI and Machine Learning', 'Introduction to the probabilistic and statistical techniques used in modern computer systems. Probabilistic graphical models such as Bayesian and Markov networks. Probabilistic inference techniques including variable elimination, belief propagation and its generalizations, and sampling-based algorithms such as importance sampling and Markov Chain Monte Carlo sampling. Statistical learning techniques for learning the structure and parameters of graphical models. Sequential models such as Hidden Markov models and Dynamic Bayesian networks. Prerequisites: (CS 3341 or equivalent) and (CS 5343 or equivalent). (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(106, '6348', 'CS', 'Data and Applications Security', 'The course will teach principles, technologies, tools and trends for data and applications security. Topics to be covered include: confidentiality, privacy and trust management; secure databases; secure distributed systems; secure multimedia and object systems; secure data warehouses; data mining for security applications; assured information sharing; secure knowledge management; secure collaboration; secure digital libraries; trustworthy semantic web; biometrics; digital forensics; secure e-commerce; secure sensor information management and secure social networks. Students will take one system or application and develop a secure version of that system or application for the programming project. Prerequisites: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(107, '6349', 'CS', 'Network Security', 'This course covers theoretical and practical aspects of network security. The topics include use of cryptography for building secure communication protocols and authentication systems; security handshake pitfalls, Kerberos and PKI, security of TCP/IP protocols including IPsec, BGP security, VPNs, IDSes, firewalls, and anonymous routing; security of TCP/IP applications; wireless LAN security; denial-of-service defense. Students are required to do a programming project building a distributed application with certain secure communication features and required to participate in several network security lab exercises and cyber war games. Prerequisites: CS 5390. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(108, '6350', 'CS', 'Big Data Management and Analytics', 'This course focuses on scalable data management and mining algorithms for analyzing very large amounts of data (i.e., Big Data). Included topics are: Mapreduce, NoSQL systems (e.g., key-value stores, column-oriented data stores, stream processing systems), association rule mining, large scale supervised and unsupervised learning, state of the art research in data streams, and applications including recommendation systems, web and big data security. Prerequisites: CS 6360 and Java programming. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(109, '6352', 'CS', 'Performance of Computer Systems and Networks', 'Overview of case studies. Quick review of principles of probability theory. Queuing models and physical origin of random variables used in queuing models. Various important cases of the M/M/m/N queuing system. Little\'s law. The M/G/1 queuing system. Simulation of queuing systems. Product form solutions of open and closed queuing networks. Convolution algorithms and Mean Value Analysis for closed queuing networks. Discrete time queuing systems. Prerequisites: ENCS majors only and a first course on probability theory. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(110, '6353', 'CS', 'Compiler Construction', 'Lexical analyzers, context-free grammars. Top-down and bottom-up parsing; shift reduce and LR parsing. Operator-precedence, recursive-descent, predictive, and LL parsing. LR(k), LL(k) and precedence grammars will be covered. Prerequisites: CS 5343 and CS 5349. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(111, '6356', 'CS', 'Software Maintenance, Evolution, and Re-Engineering', 'Principles and techniques of software maintenance. Impact of software development process on software justifiability, maintainability, evolvability, and planning of release cycles. Use of very high-level languages and dependencies for forward engineering and reverse engineering. Achievements, pitfalls, and trends in software reuse, reverse engineering, and re-engineering. Prerequisite: SE 5354. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(112, '6359', 'CS', 'Object-Oriented Analysis and Design', 'Analysis and practice of modern tools and concepts that can help produce software that is tolerant of change. Consideration of the primary tools of encapsulation and inheritance. Construction of software-ICs which show the parallel with hardware construction. Prerequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(113, '6360', 'CS', 'Database Design', 'Methods, principles, and concepts that are relevant to the practice of database software design. Database system architecture; conceptual database models; relational and object-oriented databases; database system implementation; query processing and optimization; transaction processing concepts, concurrency, and recovery; security. Prerequisite: CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(114, '6361', 'CS', 'Advanced Requirements Engineering', 'System and software requirements engineering. Identification, elicitation, modeling, analysis, specification, management, and evolution of functional and non-functional requirements. Strengths and weaknesses of different techniques, tools, and object-oriented methodologies. Interactions and trade-offs among hardware, software, and organization. System and sub-system integration with software and organization as components of complex, composite systems. Transition from requirements to design. Critical issues in requirements engineering. Prerequisite: SE 5354. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(115, '6363', 'CS', 'Design and Analysis of Computer Algorithms', 'The study of efficient algorithms for various computational problems. Algorithm design techniques. Sorting, manipulation of data structures, graphs, matrix multiplication, and pattern matching. Complexity of algorithms, lower bounds, NP completeness. Prerequisites: CS 5333 and CS 5343. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(116, '6364', 'CS', 'Artificial Intelligence', 'Design of machines that exhibit intelligence. Particular topics include: representation of knowledge, vision, natural language processing, search, logic and deduction, expert systems, planning, language comprehension, and machine learning. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(117, '6365', 'CS', 'Data and Text Mining for Computational Biology', 'The course introduces data and text mining as practiced currently in the bioinformatics field. Major topics include: sequence alignment for determining similarity between proteins and genes; properties of similarities and distances; genomic, proteomic, and text databases in the real world; finding patterns (motifs) in genes and proteins; differentiating between valid patterns and noise; classification; clustering and its application to phylogenetic trees; and selected topics from text mining. Prerequisite: CS 6325. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(118, '6366', 'CS', 'Computer Graphics', 'Device and logical coordinate systems. Geometric transformations in two and three dimensions. Algorithms for basic 2-D drawing primitives, such as Brensenham\'s algorithm for lines and circles, Bezier and B-Spline functions for curves, and line and polygon clipping algorithms. Perspectives in 3-D, and hidden-line and hidden-face elimination, such as Painter\'s and Z-Buffer algorithms. Fractals and the Mandelbrot set. Prerequisites: CS 5330 and CS 5343 and MATH 2418. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(119, '6367', 'CS', 'Software Testing, Validation and Verification', 'Fundamental concepts of software testing. Functional testing. GUI based testing tools. Control flow based test adequacy criteria. Data flow based test adequacy criteria. White box based testing tools. Mutation testing and testing tools. Relationship between test adequacy criteria. Finite state machine based testing. Static and dynamic program slicing for testing and debugging. Software reliability. Formal verification of program correctness. Prerequisite: SE 5354. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(120, '6368', 'CS', 'Telecommunication Network Management', 'In-depth study of network management issues and standards in telecommunication networks. OSI management protocols including CMIP, CMISE, SNMP, and MIB. ITU\'s TMN (Telecommunication Management Network) standards, TMN functional architecture and information architecture. NMF (Network Management Forum) and service management, service modeling and network management API. Issues of telecommunication network management in distributed processing environment. Prerequisite: CS 5390. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(121, '6369', 'CS', 'Complexity of Combinatorial Algorithms', 'Topics include bounded reducibility and completeness, approximation algorithms and heuristics for NP-hard problems, randomized algorithms, and additional complexity classes. Prerequisite: CS 6363. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(122, '6371', 'CS', 'Advanced Programming Languages', 'Functional programming, Lambda calculus, logic programming, abstract syntax, denotational semantics of imperative languages, fixpoints semantics, verification of programs, partial evaluation, interpretation and automatic compilation, axiomatic semantics, applications of semantics to software engineering. Prerequisites: CS 5343 and CS 5349. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(123, '6373', 'CS', 'Intelligent Systems', 'Logical formalizations of knowledge for the purpose of implementing intelligent systems that can reason in a way that mimics human reasoning. Topics include: syntax and semantics of common logic, description logic, modal epistemic logic; reasoning about uncertainties, beliefs, defaults and counterfactuals; reasoning within contexts; implementations of knowledge base and textual inference reasoning systems; and applications. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(124, '6374', 'CS', 'Computational Logic', 'Methods and algorithms for the solution of logic problems. Topics include problem formulation in first order logic and extensions, theorem proving algorithms, polynomially solvable cases, logic programming, and applications. Prerequisites: CS 5343 and knowledge of C. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(125, '6375', 'CS', 'Machine Learning', 'Algorithms for training perceptions and multi-layer neural nets: back propagation, Boltzmann machines, and self-organizing nets. The ID3 and the Nearest Neighbor algorithms. Formal models for analyzing learnability: exact identification in the limit and probably approximately correct (PAC) identification. Computational limitations of learning machines. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(126, '6376', 'CS', 'Parallel Processing', 'Topics include parallel processing, parallel machine models, parallel algorithms for sorting, searching and matrix operations. Parallel graph algorithms. Prerequisite: CS 5343. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(127, '6377', 'CS', 'Introduction to Cryptography', 'This course covers the basic aspects of modern cryptography, including block ciphers, pseudorandom functions, symmetric encryption, Hash functions, message authentication, number-theoretic primitives, public-key encryption, digital signatures and zero knowledge proofs. Prerequisites: CS 5333 and CS 5343 and ENCS majors only. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(128, '6378', 'CS', 'Advanced Operating Systems', 'Concurrent processing, inter-process communication, process synchronization, deadlocks, introduction to queuing theory and operational analysis, topics in distributed systems and algorithms, checkpointing, recovery, multiprocessor operating systems. Must have knowledge of C, UNIX, and Socket Programming. Prerequisites: CS 5343 and CS 5348 or equivalent. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(129, '6379', 'CS', 'Biological Database Systems and Data Mining', 'Relational data models and database management systems; theories and techniques of constructing relational databases to store biological data, including sequences, structures, genetic linkages and maps, and signal pathways. Introduction to a relational database query language (SQL) with emphasis on answering biologically important questions. Summary of current biological databases. Data integration from various sources and security. Novel data mining methods in bioinformatics with an emphasis on protein structure prediction, homology search, genomic sequence analysis, gene finding and gene mapping. Future directions for biological database development. Prerequisites: (BIOL 6373 or BMEN 6391) and BIOL 5381 and CS 5343 or instructor consent required. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(130, '6380', 'CS', 'Distributed Computing', 'Topics include distributed algorithms, election algorithms, synchronizers, mutual exclusion, resource allocation, deadlocks, Byzantine agreement and clock synchronization, knowledge and common knowledge, reliability in distributed networks, and proving distributed programs correct. Prerequisite: CS 5348. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(131, '6381', 'CS', 'Combinatorics and Graph Algorithms', 'Fundamentals of combinatorics and graph theory. Combinatorial optimization, optimization algorithms for graphs (max flow, shortest routes, Euler tour, Hamiltonian tour). Prerequisites: CS 5343 and CS 6363. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(132, '6382', 'CS', 'Theory of Computation', 'Formal models of computation. Recursive function theory. Undecidability and incompleteness. Selected topics in theory of computation. Instructor consent required. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(133, '6383', 'CS', 'Computational Systems Biology', 'The course will provide a system-level understanding of biological systems by analyzing biological data using computational techniques. The major topics include: computational inference of biological networks (regulatory, protein interactions, and metabolic) and the effects of biological networks in cellular processes, development, and disease. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(134, '6384', 'CS', 'Computer Vision', 'Algorithms for extracting information from digital pictures. Particular topics include: analysis of motion in time varying image sequences, recovering depth from a pair of stereo images, image separation, recovering shape from textured images and shadows, object matching techniques, model based recognition, and the Hough transform. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(135, '6385', 'CS', 'Algorithmic Aspects of Telecommunication Networks', 'This is an advanced course on topics related to the design, analysis, and development of telecommunications systems and networks. The focus is on the efficient algorithmic solutions for key problems in modern telecommunications networks, in centralized and distributed models. Topics include: main concepts in the design of distributed algorithms in synchronous and asynchronous models, analysis techniques for distributed algorithms, centralized and distributed solutions for handling design and optimization problems concerning network topology, architecture, routing, survivability, reliability, congestion, dimensioning and traffic management in modern telecommunication networks. Prerequisites: CS 5343 and CS 5348 and (CS 3341 or ENGR 3341 or equivalent). (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(136, '6386', 'CS', 'Telecommunication Software Design', 'Programming with sockets and remote procedure calls, real time programming concepts and strategies. Operating system design for real time systems. Encryption, file compression, and implementation of fire walls. An in-depth study of TCP/IP implementation. Introduction to discrete event simulation of networks. Prerequisite: CS 5390. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(137, '6390', 'CS', 'Advanced Computer Networks', 'Survey of recent advancements in high-speed network technologies. Application of quantitative approach to the study of broadband integrated networks including admission control, access control, and quality of service guarantee. Prerequisite: CS 5390. (3-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(138, '6391', 'CS', 'Optical Networks', 'Enabling technologies for optical networks. Wavelength-division multiplexing. Broadcast-and-select optical networks. Wavelength-routed optical networks. Virtual topology design. Routing and wavelength assignment. Network control and management. Protection and restoration. Wavelength conversion. Traffic grooming. Photonic packet switching. Optical burst switching. Survey of recent advances in optical networking. Prerequisites: CS 5390 and (CS 6352 or CS 6385 or CS 6390). (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(139, '6392', 'CS', 'Mobile Computing Systems', 'Topics include coping with mobility of computing systems, data management, reliability issues, packet transmission, mobile IP, end-to-end reliable communication, channel and other resource allocation, slot assignment, routing protocols, and issues in mobile wireless networks (without base stations). Prerequisite: CS 6378 or CS 6390. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(140, '6393', 'CS', 'Advanced Algorithms in Biology', 'Recent advanced topics in algorithms in biology will be discussed. Topics will be chosen from: sorting and transformational operations on strings and permutations, structural analysis of proteins, pooling design and nonadaptive group testing, approximation algorithms, and complexity issues. Prerequisites: CS 6363 and CS 6325 and ENCS majors only. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(141, '6395', 'CS', 'Speech Recognition, Synthesis, and Understanding', 'Basic speech processing techniques: isolated word recognition using dynamic time warping, acoustic modeling using hidden Markov models, statistical language modeling, search algorithms in large vocabulary continuous speech recognition, components in text-to-speech systems, and architecture and components in spoken dialog systems. Prerequisite: CS 5343. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(142, '6396', 'CS', 'Real-Time Systems', 'Introduction to real-time applications and concepts. Real-time operating systems and resource management. Specification and design methods for real-time systems. System performance analysis and optimization techniques. Project to specify, analyze, design, implement and test small real-time system. Prerequisite: CS 5348. (3-0) R', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(143, '6397', 'CS', 'Synthesis and Optimization of High-Performance Systems', 'A comprehensive study of high-level synthesis and optimization algorithms for designing high performance systems with multiple CPUs or functional units for critical applications such as Multimedia, Signal processing, Telecommunications, Networks, and Graphics applications, etc. Topics including algorithms for architecture-level synthesis, scheduling, resource binding, real-time systems, parallel processor array design and mapping, code generations for DSP processors, embedded systems and hardware/software codesigns. Prerequisite: CS 5343. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(144, '6398', 'CS', 'DSP Architectures', 'Typical DSP algorithms, representation of DSP algorithms, data-graph, FIR filters, convolutions, Fast Fourier Transform, Discrete Cosine Transform, low power design, VLSI implementation of DSP algorithms, implementation of DSP algorithms on DSP processors, DSP applications including wireless communication and multimedia. Prerequisite: CS 5343. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(145, '6399', 'CS', 'Parallel Architectures and Systems', 'A comprehensive study of the fundamentals of parallel systems and architecture. Topics including parallel programming environment, fine-grain parallelism such as VLIW and superscalar, parallel computing paradigm of shared-memory, distributed-memory, data-parallel and data-flow models, cache coherence, compiling techniques to improve parallelism, scheduling theory, loop transformations, loop parallelizations and run-time systems. Prerequisite: CS 5348. (3-0) T', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(146, '6V81', 'CS', 'Independent Study in Computer Science', 'May be repeated for credit. Prerequisite: ENCS majors only and instructor consent required. ([1-9]-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(147, '6V98', 'CS', 'Thesis', 'Pass/Fail only. May be repeated for credit. Instructor consent required. Prerequisite: ENCS majors only. ([3-9]-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(148, '7301', 'CS', 'Recent Advances in Computing', 'Advanced topics and publications will be selected from the theory, design, and implementation issues in computing. May be repeated for credit as topics vary. Prerequisites: ENCS majors only and instructor consent required. (3-0) Y', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(149, '8V02', 'CS', 'Topics in Computer Science', 'Pass/Fail only. May be repeated for credit (9 semester credit hours maximum). Instructor consent required. Prerequisite: ENCS majors only. ([1-6]-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(150, '8V07', 'CS', 'Research', 'Open to students with advanced standing subject to approval of the graduate advisor. Pass/Fail only. May be repeated for credit. Prerequisites: ENCS majors only and instructor consent required. ([1-9]-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30'),
(151, '8V99', 'CS', 'Dissertation', 'Pass/Fail only. May be repeated for credit. Prerequisites: ENCS majors only and instructor consent required. ([1-9]-0) S', '2018-06-01 22:44:30', '2018-06-01 22:44:30');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL COMMENT 'primary key',
  `employee_name` varchar(255) NOT NULL COMMENT 'employee name',
  `employee_salary` double NOT NULL COMMENT 'employee salary',
  `employee_age` int(11) NOT NULL COMMENT 'employee age'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='datatable demo table';

-- --------------------------------------------------------

--
-- Table structure for table `prereqs`
--

CREATE TABLE `prereqs` (
  `course` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pr` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `prereqs`
--

INSERT INTO `prereqs` (`course`, `pr`) VALUES
('CS5330', 'CS5303'),
('CS5336', 'CS5303'),
('CS5343', 'CS5303'),
('CS5343', 'CS5333'),
('CS5348', 'CS5330'),
('CS5348', 'CS5343'),
('CS5349', 'CS5333'),
('CS5375', 'CS3335'),
('CS5375', 'CS5343'),
('CS5390', 'CS5343'),
('CS6301', 'CS5343'),
('CS6304', 'CS3340'),
('CS6304', 'EE4304'),
('CS6307', 'CS5303'),
('CS6307', 'CS5343'),
('CS6313', 'CS3341'),
('CS6313', 'SE3341'),
('CS6313', 'STAT3341'),
('CS6314', 'CS5343'),
('CS6315', 'CS5343'),
('CS6320', 'CS5343'),
('CS6321', 'CS6320'),
('CS6322', 'CS5343'),
('CS6323', 'CS5343'),
('CS6324', 'CS5343'),
('CS6324', 'CS5348'),
('CS6325', 'CS5343'),
('CS6326', 'CS5343'),
('CS6327', 'CS5343'),
('CS6328', 'CS5343'),
('CS6331', 'CS5343'),
('CS6332', 'CS5343'),
('CS6332', 'CS5348'),
('CS6333', 'CS6325'),
('CS6334', 'CS5343'),
('CS6343', 'CS5343'),
('CS6343', 'CS5348'),
('CS6347', 'CS3341'),
('CS6347', 'CS5343'),
('CS6348', 'CS5343'),
('CS6349', 'CS5390'),
('CS6350', 'CS6360'),
('CS6353', 'CS5343'),
('CS6353', 'CS5349'),
('CS6356', 'SE5354'),
('CS6359', 'CS5343'),
('CS6360', 'CS5343'),
('CS6361', 'SE5354'),
('CS6363', 'CS5333'),
('CS6363', 'CS5343'),
('CS6364', 'CS5343'),
('CS6365', 'CS6325'),
('CS6366', 'CS5330'),
('CS6366', 'CS5343'),
('CS6366', 'MATH2418'),
('CS6367', 'SE5354'),
('CS6368', 'CS5390'),
('CS6369', 'CS6363'),
('CS6371', 'CS5343'),
('CS6371', 'CS5349'),
('CS6373', 'CS5343'),
('CS6374', 'CS5343'),
('CS6375', 'CS5343'),
('CS6376', 'CS5343'),
('CS6377', 'CS5333'),
('CS6377', 'CS5343'),
('CS6378', 'CS5343'),
('CS6378', 'CS5348'),
('CS6379', 'BIOL6373'),
('CS6379', 'BMEN6391'),
('CS6379', 'BIOL5381'),
('CS6379', 'CS5343'),
('CS6380', 'CS5348'),
('CS6381', 'CS5343'),
('CS6381', 'CS6363'),
('CS6384', 'CS5343'),
('CS6385', 'CS5343'),
('CS6385', 'CS5348'),
('CS6385', 'CS3341'),
('CS6385', 'ENGR3341'),
('CS6386', 'CS5390'),
('CS6390', 'CS5390'),
('CS6391', 'CS5390'),
('CS6391', 'CS6352'),
('CS6391', 'CS6385'),
('CS6391', 'CS6390'),
('CS6392', 'CS6378'),
('CS6392', 'CS6390'),
('CS6393', 'CS6363'),
('CS6393', 'CS6325'),
('CS6395', 'CS5343'),
('CS6396', 'CS5348'),
('CS6397', 'CS5343'),
('CS6398', 'CS5343'),
('CS6399', 'CS5348');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `forename` varchar(32) NOT NULL,
  `surname` varchar(32) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`forename`, `surname`, `username`, `password`) VALUES
('Bill', 'Smith', 'bsmith', '32aa0c466818e1ccba25b8793db98c94'),
('Pauline', 'Jones', 'pjones', '53eb1f29c1f8a132441a4fad1d6f667d');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`Time(minutes)`);

--
-- Indexes for table `car1`
--
ALTER TABLE `car1`
  ADD PRIMARY KEY (`Time(minutes)`);

--
-- Indexes for table `car2`
--
ALTER TABLE `car2`
  ADD PRIMARY KEY (`CarID`);

--
-- Indexes for table `car3`
--
ALTER TABLE `car3`
  ADD PRIMARY KEY (`Car3ID`);

--
-- Indexes for table `carlist`
--
ALTER TABLE `carlist`
  ADD PRIMARY KEY (`Time(minutes)`);

--
-- Indexes for table `cats`
--
ALTER TABLE `cats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classics`
--
ALTER TABLE `classics`
  ADD PRIMARY KEY (`isbn`),
  ADD KEY `author` (`author`(20)),
  ADD KEY `title` (`title`(20)),
  ADD KEY `category` (`category`(4)),
  ADD KEY `year` (`year`),
  ADD KEY `isbn` (`isbn`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `car2`
--
ALTER TABLE `car2`
  MODIFY `CarID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `car3`
--
ALTER TABLE `car3`
  MODIFY `Car3ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cats`
--
ALTER TABLE `cats`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
